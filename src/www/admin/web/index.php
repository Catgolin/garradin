<?php

namespace Garradin;

use Garradin\Web\Web;
use Garradin\Entities\Web\Page;

require_once __DIR__ . '/_inc.php';

$parent = qg('parent') ?: '';
$cat = null;

if ($parent) {
	$cat = Web::getByURI($parent);

	if (!$cat) {
		throw new UserException('Catégorie inconnue');
	}
}

Web::sync($parent);

$order_date = qg('order_title') === null;

$categories = Web::listCategories($cat ? $cat->path : '');
$pages = Web::listPages($cat ? $cat->path : '', $order_date);
$title = $parent ? sprintf('Gestion du site web : %s', $cat->title) : 'Gestion du site web';
$type_page = Page::TYPE_PAGE;
$type_category = Page::TYPE_CATEGORY;
$breadcrumbs = $cat ? $cat->getBreadcrumbs() : [];

$tpl->assign(compact('categories', 'pages', 'title', 'parent', 'type_page', 'type_category', 'order_date', 'breadcrumbs'));

$tpl->display('web/index.tpl');
