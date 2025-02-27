<?php

namespace Garradin\Accounting;

use Garradin\CSV_Custom;
use Garradin\UserException;
use Garradin\Utils;
use Garradin\Membres\Session;
use Garradin\Entities\Accounting\Transaction;
use Garradin\Entity;

/**
 * Provides assisted reconciliation
 */
class AssistedReconciliation
{
	const COLUMNS = [
		'label'          => 'Libellé',
		'date'           => 'Date',
		//'notes'          => 'Remarques',
		//'reference'      => 'Numéro pièce comptable',
		//'p_reference'    => 'Référence paiement',
		'amount'         => 'Montant',
		'debit'          => 'Débit',
		'credit'         => 'Crédit',
		'balance'        => 'Solde',
	];

	protected $csv;

	public function __construct()
	{
		$this->csv = new CSV_Custom(Session::getInstance(), 'acc_reconcile_csv');
		$this->csv->setColumns(self::COLUMNS);
		$this->csv->setMandatoryColumns(['label', 'date']);
		$this->csv->setModifier(function (\stdClass $line): \stdClass {
			$date = Entity::filterUserDateValue($line->date);

			$line->date = $date;

			static $has_amount = null;

			if (null === $has_amount) {
				$has_amount = in_array('amount', $this->csv->getTranslationTable());
			}

			if (!$has_amount && isset($line->credit) && isset($line->debit)) {
				$line->amount = $line->credit ?: '-' . ltrim($line->debit, '- \t\r\n');
			}

			$line->amount = Utils::moneyToInteger($line->amount ?? 0);

			if (!empty($line->balance)) {
				$line->balance = (substr($line->balance, 0, 1) == '-' ? -1 : 1) * Utils::moneyToInteger($line->balance);
			}

			$line->new_params = http_build_query([
				'a' => abs($line->amount)/100,
				'l' => $line->label,
				'd' => $date->format('Y-m-d'),
				't' => $line->amount < 0 ? Transaction::TYPE_EXPENSE : Transaction::TYPE_REVENUE,
			]);

			return $line;
		});
	}

	public function csv(): CSV_Custom
	{
		return $this->csv;
	}

	public function setSettings(array $translation_table, int $skip): void
	{
		$this->csv->setTranslationTable($translation_table);

		if (!((in_array('credit', $translation_table) && in_array('debit', $translation_table)) || in_array('amount', $translation_table))) {
			throw new UserException('Il est nécessaire de sélectionner une colonne "montant" ou deux colonnes "débit" et "crédit"');
		}

		$this->csv->skip($skip);
	}

	public function getStartAndEndDates(): ?array
	{
		$start = $end = null;

		if (!$this->csv->ready()) {
			return compact('start', 'end');
		}

		foreach ($this->csv->iterate() as $line) {
			if (null === $start || $line->date < $start) {
				$start = $line->date;
			}

			if (null === $end || $line->date > $end) {
				$end = $line->date;
			}
		}

		return compact('start', 'end');
	}

	public function mergeJournal(\Generator $journal)
	{
		$lines = [];

		$csv = iterator_to_array($this->csv->iterate());
		$journal = iterator_to_array($journal);
		$i = 0;
		$sum = 0;

		foreach ($journal as $j) {
			$id = $j->date->format('Ymd') . '.' . $i++;

			$row = (object) ['csv' => null, 'journal' => $j];

			if (isset($j->debit)) {
				foreach ($csv as &$line) {
					if (!isset($line->date)) {
						 continue;
					}

					// Match date, amount and label
					if ($j->date->format('Ymd') == $line->date->format('Ymd')
						&& ($j->credit * -1 == $line->amount || $j->debit == $line->amount)
						&& strtolower($j->label) == strtolower($line->label)) {
						$row->csv = $line;
						$line = null;
						break;
					}
				}
			}

			$lines[$id] = $row;
		}

		unset($line, $row, $j);

		// Second round to match only amount and label
		foreach ($lines as $row) {
			if ($row->csv || !isset($row->journal->debit)) {
				continue;
			}

			$j = $row->journal;

			foreach ($csv as &$line) {
				if (!isset($line->date)) {
					 continue;
				}

				if ($j->date->format('Ymd') == $line->date->format('Ymd')
					&& ($j->credit * -1 == $line->amount || $j->debit == $line->amount)) {
					$row->csv = $line;
					$line = null;
					break;
				}
			}
		}

		unset($j, $line);

		// Then add CSV lines on the right
		foreach ($csv as $line) {
			if (null == $line) {
				continue;
			}

			$id = $line->date->format('Ymd') . '.' . ($i++);
			$lines[$id] = (object) ['csv' => $line, 'journal' => null];
		}

		ksort($lines);
		$prev = null;

		foreach ($lines as &$line) {
			$line->add = false;

			if (isset($line->csv)) {
				$sum += $line->csv->amount;
				$line->csv->running_sum = $sum;

				if ($prev && ($prev->date->format('Ymd') != $line->csv->date->format('Ymd') || $prev->label != $line->csv->label)) {
					$prev = null;
				}
			}

			if (isset($line->csv) && isset($line->journal)) {
				$prev = null;
			}

			if (isset($line->csv) && !isset($line->journal) && !$prev) {
				$line->add = true;
				$prev = $line->csv;
			}
		}

		return $lines;
	}
}
