<nav class="tabs">
	<ul>
		<li{if $current == 'index'} class="current"{/if}><a href="{$admin_url}services/">Activités et cotisations</a></li>
		<li><a href="{$admin_url}membres/cotisations/ajout.php">Saisie d'une cotisation</a></li>
		{if $session->canAccess('membres', Membres::DROIT_ADMIN)}
			<li><a href="{$admin_url}services/reminders/">Gestion des rappels automatiques</a></li>
		{/if}
	</ul>
</nav>
