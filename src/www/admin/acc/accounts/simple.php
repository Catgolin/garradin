<?php
namespace Garradin;

use Garradin\Accounting\Transactions;
use Garradin\Entities\Accounting\Transaction;

require_once __DIR__ . '/../_inc.php';

if (!CURRENT_YEAR_ID) {
	Utils::redirect(ADMIN_URL . 'acc/years/?msg=OPEN');
}

$year = $current_year;

$types = [
	-1 => 'Toutes',
	Transaction::TYPE_REVENUE => 'Recettes',
	Transaction::TYPE_EXPENSE => 'Dépenses',
	Transaction::TYPE_TRANSFER => 'Virements',
	Transaction::TYPE_DEBT => 'Dettes',
	Transaction::TYPE_CREDIT => 'Créances',
	Transaction::TYPE_ADVANCED => 'Saisies avancées',
];

$type = qg('type');

if (!array_key_exists($type, $types)) {
	$type = key($types);
}

$list = Transactions::listByType(CURRENT_YEAR_ID, $type == -1 ? null : $type);
$list->setTitle(sprintf('Suivi - %s', $types[$type]));
$list->loadFromQueryString();

$can_edit = $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_ADMIN) && !$year->closed;

$tpl->assign(compact('type', 'list', 'types', 'can_edit', 'year'));

$tpl->display('acc/accounts/simple.tpl');
