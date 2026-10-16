// Run with: node tests/merged_section_save_test.cjs
// Exercise the actual view helper without a server or database.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const view = fs.readFileSync(path.join(__dirname, '../app/Views/form_view.php'), 'utf8');
const start = view.indexOf('function prepareMergedSave(button)');
const end = view.indexOf("document.querySelectorAll('[data-merged-save]", start);
const helper = view.slice(start, end).replaceAll('<?= csrf_token() ?>', 'csrf_test');
const forms = new Map();
const elements = new Map();
const document = {
    querySelector(selector) {
        return forms.get(Number(selector.match(/data-section-id="(\d+)"/)[1]));
    },
    getElementById(id) { return elements.get(id); },
    createElement() {
        return {
            children: [],
            appendChild(child) { this.children.push(child); },
            remove() { elements.delete(this.id); },
        };
    },
    body: { appendChild(element) { elements.set(element.id, element); } },
};
class FixtureFormData {
    constructor(form) { this.entries = form.entries; }
    forEach(callback) { this.entries.forEach(([name, value]) => callback(value, name)); }
}
const context = vm.createContext({ document, FormData: FixtureFormData });
vm.runInContext(helper, context);
function member(id, valid = true) {
    return {
        action: '/form/submit',
        reportValidity: () => valid,
        entries: [
            ['csrf_test', 'token'],
            [`form_id[${id}]`, '1'],
            [`asr_id[${id}]`, '83'],
            [`table_name[${id}]`, 'form_values'],
            [`action_flag[${id}]`, 'editable'],
            [`sections[${id}][shared_name][0]`, `value-${id}`],
            [`sections[${id}][shared_name][1]`, `row-${id}`],
            ['section_id', String(id)], ['asr_id', '83'],
            ['password', 'stale'], ['save_type', 'stale'],
        ],
    };
}
forms.set(4, member(4));
forms.set(5, member(5));
forms.set(6, member(6));
const button = { dataset: { mergedSave: '[4,5]' }, value: 'draft' };
let target = context.prepareMergedSave(button);
const payload = new Map(target.children.map(input => [input.name, input.value]));
assert.equal(target.action, '/form/submit');
assert.equal(target.method, 'POST');
for (const id of [4, 5]) {
    assert.equal(payload.get(`form_id[${id}]`), '1');
    assert.equal(payload.get(`asr_id[${id}]`), '83');
    assert.equal(payload.get(`sections[${id}][shared_name][0]`), `value-${id}`);
    assert.equal(payload.get(`sections[${id}][shared_name][1]`), `row-${id}`);
}
assert.equal(payload.has('form_id[6]'), false);
assert.equal(payload.has('section_id'), false);
assert.equal(payload.has('asr_id'), false);
assert.equal(payload.has('password'), false);
assert.equal(payload.get('save_type'), 'draft');
assert.equal(target.children.filter(input => input.name === 'csrf_test').length, 1);
button.value = 'submit';
const oldTarget = target;
target = context.prepareMergedSave(button);
assert.notEqual(target, oldTarget);
assert.equal(elements.get('mergedSaveForm'), target);
assert.equal(target.children.find(input => input.name === 'save_type').value, 'submit');
forms.set(5, member(5, false));
assert.equal(context.prepareMergedSave(button), null, 'Invalid member fields must prevent the group save.');
forms.delete(5);
assert.equal(context.prepareMergedSave(button), null, 'A missing member form must prevent a partial submission.');
console.log('Merged save payload and validation checks passed. No database used.');
