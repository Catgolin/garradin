<?php
use Garradin\Entities\Accounting\Account;
?>
{include file="admin/_head.tpl" title="Tous les comptes" current="acc/accounts"}

{include file="acc/_year_select.tpl"}

{include file="acc/accounts/_nav.tpl" current="all"}

{include file="acc/_simple_help.tpl" link="../reports/trial_balance.php?year=%d"|args:$current_year.id type=null}

{if !empty($balance)}
<table class="list">
	<thead>
		<tr>
			<td>Numéro</td>
			<th>Compte</th>
			<td class="money">Total des débits</td>
			<td class="money">Total des crédits</td>
			<td class="money">Solde</td>
		</tr>
	</thead>
	<tbody>
	{foreach from=$balance item="account"}
		<tr class="{if $account.balance === 0}disabled{/if}">
			<td class="num">
				<a href="{$admin_url}acc/accounts/journal.php?id={$account.id}&amp;year={$current_year.id}">{$account.code}</a>
			</td>
			<th>{$account.label}</th>
			<td class="money{if !$account.debit} disabled{/if}">{$account.debit|raw|money:false}</td>
			<td class="money{if !$account.credit} disabled{/if}">{$account.credit|raw|money:false}</td>
			<td class="money">{if $account.balance !== null}<b>{$account.balance|escape|money:false}</b>{/if}</td>
		</tr>
	{/foreach}
	</tbody>
</table>
{else}
	<div class="alert block">
		<p>Aucun compte ne comporte d'écriture sur cet exercice.</p>
		<p>
			{linkbutton href="!acc/transactions/new.php" label="Saisir une écriture" shape="plus"}
		</p>
	</div>
{/if}

<p class="help">
	Note : n'apparaissent ici que les comptes qui ont été utilisés dans cet exercice (au moins une écriture).<br />
	Les lignes grisées correspondent aux comptes soldés.<br />
	Pour voir la liste complète des comptes, même ceux qui n'ont pas été utilisés, se référer au <a href="{$admin_url}acc/charts/accounts/?id={$current_year.id_chart}">plan comptable</a>.
</p>

{include file="admin/_foot.tpl"}