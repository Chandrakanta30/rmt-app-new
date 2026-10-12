<?php

// Run with: php tests/section_layout_test.php
// Uses fixtures only; never connects to the shared database.
require_once dirname(__DIR__) . '/app/Libraries/SectionLayout.php';

use App\Libraries\SectionLayout;

function checkLayout(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function layoutSection(int $id, array $overrides = []): array
{
    return array_replace([
        'id' => $id, 'form_id' => 1, 'title' => 'Section ' . $id,
        'parent_section_id' => null, 'merge_tag' => null,
        'position' => $id, 'order' => $id, 'layout' => 'grid', 'table' => null,
        'fields' => [['name' => 'shared_name', 'label' => 'Value', 'type' => 'text', 'grid_size' => 6]],
    ], $overrides);
}

$layout = new SectionLayout();
checkLayout($layout->build([]) === [], 'Empty forms must remain empty.');
$fixtures = [
    layoutSection(3, ['parent_section_id' => 1, 'position' => 1]),
    layoutSection(5, ['merge_tag' => 'Merged group', 'position' => 4]),
    layoutSection(2, ['parent_section_id' => 1, 'position' => 0]),
    layoutSection(1, ['position' => 0]),
    layoutSection(4, ['merge_tag' => 'Merged group', 'position' => 3]),
    layoutSection(6, ['parent_section_id' => 5, 'position' => 0]),
];
$groups = $layout->build($fixtures);
checkLayout(count($groups) === 2, 'Children and merged roots should share their outer panels.');
checkLayout(array_column(array_column($groups[0]['entries'], 'section'), 'id') === [1, 2, 3], 'Children must follow their parent in sibling order.');
checkLayout(array_column($groups[0]['entries'], 'number') === ['1', '1.1', '1.2'], 'Subsection numbering must follow the hierarchy.');
checkLayout($groups[1]['title'] === 'Merged group', 'Merged groups must use the saved merge title.');
checkLayout(array_column(array_column($groups[1]['entries'], 'section'), 'id') === [4, 5, 6], 'Merged members must retain their children and original IDs.');
checkLayout(array_column($groups[1]['entries'], 'number') === ['2', '2', '2.1'], 'Merged normal sections must use whole numbers; only children use dotted numbers.');
$mergedChildren = $layout->build([
    layoutSection(1),
    layoutSection(2, ['parent_section_id' => 1, 'merge_tag' => 'Children']),
    layoutSection(3, ['parent_section_id' => 1, 'merge_tag' => 'Children']),
    layoutSection(4, ['parent_section_id' => 1]),
]);
checkLayout(array_column($mergedChildren[0]['entries'], 'number') === ['1', '1.1', '1.2', '1.3'], 'Merging must not introduce an extra subsection level.');
checkLayout(array_column($mergedChildren[0]['entries'], 'show_metadata') === [true, false, true, true], 'Merged subsections must share one footer while separate subsections keep theirs.');

$separateForms = $layout->build([
    layoutSection(1, ['merge_tag' => 'Same title']),
    layoutSection(2, ['form_id' => 2, 'merge_tag' => 'Same title']),
]);
checkLayout(count($separateForms) === 2, 'Identical merge tags in different forms must stay separate.');
$differentParents = $layout->build([
    layoutSection(1), layoutSection(2),
    layoutSection(3, ['parent_section_id' => 1, 'merge_tag' => 'Same title']),
    layoutSection(4, ['parent_section_id' => 2, 'merge_tag' => 'Same title']),
]);
checkLayout(count($differentParents[0]['entries']) === 2 && count($differentParents[1]['entries']) === 2, 'Merge tags must remain scoped to their parent.');
$invalid = $layout->build([
    layoutSection(1, ['parent_section_id' => 2]),
    layoutSection(2, ['parent_section_id' => 1]),
    layoutSection(3, ['parent_section_id' => 999]),
    layoutSection(4, ['form_id' => 2, 'parent_section_id' => 3]),
]);
checkLayout(count($invalid) === 4, 'Cycles, missing parents, and cross-form parents must not hide sections.');

// Render the real view with fixtures to check HTML and value ownership.
chdir(dirname(__DIR__));
require 'system/Test/bootstrap.php';
helper(['form', 'url']);
$fixtureValues = [];
foreach ($fixtures as $section) {
    $fixtureValues[$section['id']] = ['shared_name' => 'saved-' . $section['id']];
}
// Exercise all three layouts while retaining identical field names.
$fixtures[0]['layout'] = 'inline';
$fixtures[0]['inline_template'] = '<p>Inline value: {shared_name}</p>';
$fixtures[0]['formatting'] = ['font_family' => 'arial', 'font_size_pt' => 16, 'text_alignment' => 'center'];
$fixtures[1]['layout'] = 'table';
$fixtures[1]['inline_template'] = "[Value|header]\n{shared_name}";
$fixtures[4]['title'] = 'Section 1';
$fixtures[1]['title'] = 'Custom member title';
$html = view('form_view', [
    'form' => ['id' => 1, 'name' => 'Fixture form', 'form_key' => 'fixture', 'status' => 'created'],
    'sections' => $fixtures, 'values' => $fixtureValues, 'sectionMetadata' => [],
    'readonly' => false, 'canEdit' => true, 'asrId' => 0, 'breadcrumb' => 'Fixture',
]);
$document = new DOMDocument();
libxml_use_internal_errors(true);
$document->loadHTML($html);
$xpath = new DOMXPath($document);
checkLayout($xpath->query('//form//form')->length === 0, 'Section forms must never be nested.');
checkLayout($xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," section-group ")]')->length === 2, 'The view must render two grouped panels.');
foreach ($fixtures as $section) {
    $id = $section['id'];
    $inputs = $xpath->query('//form[@data-section-id="' . $id . '"]//input[@name="sections[' . $id . '][shared_name]"]');
    checkLayout($inputs->length === 1, 'Each original section must render exactly once.');
    checkLayout($inputs->item(0)->getAttribute('value') === 'saved-' . $id, 'Fields with identical names must retain section-specific values.');
}
checkLayout(str_contains($html, 'Subsection 1.1'), 'Subsection labels must appear in the rendered view.');
checkLayout($xpath->query('//form[@data-section-id="1"]//h3')->length === 0, 'The parent heading must not repeat inside its section.');
checkLayout($xpath->query('//form[@data-section-id="4"]//h3')->length === 1, 'The first merged member must show only the merge title in its content header.');
checkLayout($xpath->query('//form[@data-section-id="4"]//div[@class="section-entry-header"]/div/h3')->item(0)->textContent === 'Merged group', 'The merge title must align with the first member status badges.');
checkLayout($xpath->query('//form[@data-section-id="5"]//div[@class="section-entry-header"]/div/h3')->item(0)->textContent === 'Custom member title', 'Later custom member titles must use the same header position.');
checkLayout($xpath->query('//div[@class="section-panel-header"]//h2')->length === 1, 'Default group titles must not repeat the outer section number; custom merge titles must remain visible.');
checkLayout($xpath->query('//form[@data-section-id="6"]//h3')->length === 0, 'Default subsection titles must not repeat subsection labels.');
checkLayout($xpath->query('//form[@data-section-id="5"]//h3')->length === 1, 'Distinct merged member titles must remain visible.');
checkLayout($xpath->query('//form[contains(@class,"section-entry") and not(contains(@class,"section-subsection"))]//span[@class="section-kicker"]')->length === 0, 'Normal members must not repeat the outer section number.');
$inlineForm = $xpath->query('//form[@data-section-id="3"]')->item(0);
checkLayout(str_contains($inlineForm->getAttribute('style'), '--section-font-size:16pt;'), 'Saved font size must be respected.');
checkLayout(str_contains($inlineForm->getAttribute('style'), '--section-font-family:Arial'), 'Saved font family must be respected.');
checkLayout($xpath->query('//form[@data-section-id="5"]//table')->length === 1, 'Merged table sections must retain their table layout.');
checkLayout($xpath->query('//form[@data-section-id="4"]//div[@class="section-meta-footer"]')->length === 0, 'Earlier merged members must not repeat the metadata footer.');
checkLayout($xpath->query('//form[@data-section-id="5"]//div[@class="section-meta-footer"]')->length === 1, 'The final merged member must render the shared metadata footer.');
checkLayout($xpath->query('//div[@class="section-meta-footer"]')->length === 5, 'Unmerged sections and subsections must retain their metadata footers.');
checkLayout($xpath->query('//form[@data-section-id="4"]//button[@name]')->length === 0, 'Earlier merged members must not show duplicate save buttons.');
$sharedSaveButtons = $xpath->query('//form[@data-section-id="5"]//button[@data-merged-save]');
checkLayout($sharedSaveButtons->length === 2, 'Merged sections must share one Save and one Save as Draft action.');
checkLayout(json_decode($sharedSaveButtons->item(0)->getAttribute('data-merged-save'), true) === [4, 5], 'Shared save must include both original section IDs and exclude independent subsections.');
checkLayout($sharedSaveButtons->item(0)->getAttribute('type') === 'button', 'Shared save must collect all member forms before submitting.');

$asrHtml = view('form_view', [
    'form' => ['id' => 1, 'name' => 'Fixture form', 'form_key' => 'fixture', 'status' => 'created'],
    'sections' => $fixtures, 'values' => $fixtureValues,
    'sectionMetadata' => [4 => ['status' => 'approved'], 5 => ['status' => 'rejected']],
    'readonly' => false, 'canEdit' => true, 'asrId' => 83, 'breadcrumb' => 'Fixture',
]);
$document->loadHTML($asrHtml);
$xpath = new DOMXPath($document);
$asrSaveButtons = $xpath->query('//form[@data-section-id="5"]//button[@data-merged-save]');
checkLayout($asrSaveButtons->length === 2, 'An editable merged member must retain shared save actions when another is approved.');
checkLayout(json_decode($asrSaveButtons->item(0)->getAttribute('data-merged-save'), true) === [5], 'Shared saves must exclude approved members to preserve their data and status.');
checkLayout($asrSaveButtons->item(0)->getAttribute('data-asr-sign-action') === 'save', 'Shared ASR saves must still require a signature.');
checkLayout($xpath->query('//form[@data-section-id="4"]/fieldset[@disabled]')->length === 1, 'Approved merged members must remain disabled.');

$nestedHtml = view('form_view', [
    'form' => ['id' => 1, 'name' => 'Fixture form', 'form_key' => 'fixture', 'status' => 'created'],
    'sections' => [layoutSection(1), layoutSection(2, ['parent_section_id' => 1, 'merge_tag' => 'Children']), layoutSection(3, ['parent_section_id' => 1, 'merge_tag' => 'Children'])],
    'values' => [], 'sectionMetadata' => [],
    'readonly' => false, 'canEdit' => true, 'asrId' => 83, 'breadcrumb' => 'Fixture',
]);
$document->loadHTML($nestedHtml);
$xpath = new DOMXPath($document);
checkLayout($xpath->query('//form[@data-section-id="2"]//button[@name]')->length === 0, 'Merged subsections must also suppress duplicate save actions.');
$nestedSave = $xpath->query('//form[@data-section-id="3"]//button[@data-merged-save]')->item(0);
checkLayout(json_decode($nestedSave->getAttribute('data-merged-save'), true) === [2, 3], 'Merged subsection saves must be scoped to their sibling group.');

$_GET['mode'] = 'view';
$readonlyHtml = view('form_view', [
    'form' => ['id' => 1, 'name' => 'Fixture form', 'form_key' => 'fixture', 'status' => 'created'],
    'sections' => $fixtures, 'values' => [], 'sectionMetadata' => [],
    'readonly' => true, 'canEdit' => false, 'asrId' => 0, 'breadcrumb' => 'Fixture',
]);
$document->loadHTML($readonlyHtml);
$xpath = new DOMXPath($document);
checkLayout($xpath->query('//form[@data-section-id]/fieldset[@disabled]')->length === count($fixtures), 'Every subsection must remain disabled in view mode.');
checkLayout($xpath->query('//form[@data-section-id]//button[@name]')->length === 0, 'View mode must not expose section save actions.');
checkLayout($xpath->query('//form[@data-section-id="4" or @data-section-id="5"]//div[@class="section-meta-footer"]')->length === 1, 'View mode must also render one footer for merged members.');
echo "Section layout and rendering checks passed. No database used.\n";
