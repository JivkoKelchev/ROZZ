<?php
/**
 * Създава / презарежда наследения шаблон за договори от файловете в bundle-а
 * (src/RozzBundle/Resources/contract_templates/legacy_body.html и legacy_row.html).
 *
 * Не е задължителен: наследеният шаблон се създава автоматично и при първото
 * отваряне на договор/преглед от приложението (виж DELIVERY.md, стъпка 5).
 * Този скрипт е удобен начин да го създадете изрично и да проверите резултата.
 *
 * Стартиране от КОРЕНА на проекта с PHP на XAMPP, напр.:
 *   C:\xampp\php\php.exe sql\migrations\20260608_release\seed_legacy_template.php
 *
 * Идемпотентно е — може да се изпълни повторно без странични ефекти.
 */
require __DIR__ . '/../../../app/autoload.php';
require __DIR__ . '/../../../app/AppKernel.php';

$kernel = new AppKernel('prod', false);
$kernel->boot();

$legacy = $kernel->getContainer()
    ->get('contract_template_service')
    ->syncLegacyFromFiles();

echo 'Наследеният шаблон е готов (id=' . $legacy->getId() . ").\n";
