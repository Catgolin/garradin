{include file="admin/_head.tpl" title="Adresses rejetées" current="membres/message"}

<nav class="tabs">
	<ul>
		<li><a href="message_collectif.php">Envoyer</a></li>
		<li class="current"><a href="emails.php">Adresses rejetées</a></li>
	</ul>
</nav>

{if isset($_GET['sent'])}
<p class="confirm block">
	Un message de demande de confirmation a bien été envoyé. Le destinataire doit désormais cliquer sur le lien dans ce message.
</p>
{/if}

<p class="help">
	{if !$queue_count}
		Il n'y a aucun message en attente d'envoi.
	{else}
		Il y a {$queue_count} messages dans la file d'attente, ils seront envoyés dans quelques instants.
	{/if}
</p>

{if !$list->count()}
	<p class="alert block">Aucune adresse e-mail n'a été rejetée pour le moment. Cette page présentera les adresses e-mail invalides ou qui ont demandé à se désinscrire.</p>
{else}
	{include file="common/dynamic_list_head.tpl"}

		{foreach from=$list->iterate() item="row"}
		<tr>
			<th><a href="fiche.php?id={$row.user_id}">{$row.identity}</a></th>
			<td>{$row.email}</td>
			<td>{$row.status}</td>
			<td class="num">{$row.sent_count}</td>
			<td>{$row.fail_log|escape|nl2br}</td>
			<td>{$row.last_sent|date}</td>
			<td>
				{if $row.email && $row.optout}
					{linkbutton target="_dialog" label="Rétablir" href="?verify=%s"|args:$row.email shape="check"}
				{/if}
			</td>
		</tr>

		{/foreach}
	</tbody>
	</table>

	{pagination url=$list->paginationURL() page=$list.page bypage=$list.per_page total=$list->count()}

	<div class="block help">
		<h3>Statuts possibles d'une adresse e-mail&nbsp;:</h3>
		<dl class="cotisation">
			{*
			<dt>Vérifiée</dt>
			<dd>L'adresse a déjà reçu un message et a été vérifiée manuellement par le destinataire.</dd>
			*}
			<dt>Désinscription</dt>
			<dd>Le destinataire a demandé à être désinscrit et ne recevra plus de messages.</dd>
			<dt>Invalide</dt>
			<dd>L'adresse n'existe pas ou plus. Il n'est pas possible de lui envoyer des messages.</dd>
			<dt>Trop d'erreurs</dt>
			<dd>Le service destinataire a renvoyé une erreur temporaire plus de {$max_fail_count} fois.<br />Cela arrive par exemple si vos messages sont vus comme du spam trop souvent, ou si la boîte mail destinataire est pleine. Cette adresse ne recevra plus de message.</dd>
		</dl>
	</div>

{/if}

{include file="admin/_foot.tpl"}