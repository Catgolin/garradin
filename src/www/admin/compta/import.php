<?php
namespace Garradin;

require_once __DIR__ . '/_inc.php';

$session->requireAccess('compta', Membres::DROIT_ADMIN);

$e = new Compta\Exercices;
$import = new Compta\Import;

$form_errors = [];

if (qg('export') !== null)
{
    header('Content-type: application/csv');
    header('Content-Disposition: attachment; filename="Export comptabilité - ' . $config->get('nom_asso') . ' - ' . date('Y-m-d') . '.csv"');
    $import->toCSV($e->getCurrentId());
    exit;
}

$error = false;

if (f('import'))
{
    fc('compta_import', [
        'upload' => 'file|required',
        'type'   => 'required|in:citizen,garradin',
    ], $form_errors);

    if (count($form_errors) === 0)
    {
        try
        {
            if (f('type') == 'citizen')
            {
                $import->fromCitizen($_FILES['upload']['tmp_name']);
            }
            elseif (f('type') == 'garradin')
            {
                $import->fromCSV($_FILES['upload']['tmp_name']);
            }
            else
            {
                throw new UserException('Import inconnu.');
            }

            Utils::redirect('/admin/compta/import.php?ok');
        }
        catch (UserException $e)
        {
            $form_errors[] = $e->getMessage();
        }
    }
}

$tpl->assign('form_errors', $form_errors);
$tpl->assign('ok', qg('ok'));

$tpl->display('admin/compta/import.tpl');
