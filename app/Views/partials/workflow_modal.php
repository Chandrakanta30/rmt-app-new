<?php

?>
<style>
    .wf-actions { display: inline-flex; gap: 0.35rem; align-items: center; }

    .wf-btn {
        width: 30px;
        height: 30px;
        display: grid;
        place-items: center;
        border-radius: 7px;
        border: 1px solid transparent;
        cursor: pointer;
        font-size: 0.95rem;
        line-height: 1;
        transition: transform 0.08s ease, filter 0.15s ease;
    }
    .wf-btn:hover { transform: translateY(-1px); filter: brightness(0.96); }
    .wf-btn:focus-visible { outline: 2px solid #289672; outline-offset: 2px; }

    .wf-primary { background: #eef4ff; border-color: #c9dbfb; color: #1d4ed8; }
    .wf-success { background: #edf8f3; border-color: #bfe6d6; color: #15704e; }
    .wf-danger  { background: #fdeeee; border-color: #f5c9c9; color: #b42318; }

    .wf-none { color: #9aa8b5; }

    .wf-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: none;
        place-items: center;
        z-index: 1000;
        padding: 1rem;
    }
    .wf-backdrop[open] { display: grid; }

    .wf-dialog {
        background: white;
        border-radius: 12px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
        width: min(460px, 100%);
        padding: 1.4rem 1.5rem;
    }
    .wf-dialog h2 { margin: 0 0 0.3rem; font-size: 1.1rem; color: #12263a; }
    .wf-dialog p.wf-sub { margin: 0 0 1rem; color: #64748b; font-size: 0.86rem; }
    .wf-dialog label { display: block; font-size: 0.82rem; font-weight: 700; color: #46596b; margin-bottom: 0.4rem; }
    .wf-dialog textarea {
        width: 100%;
        min-height: 96px;
        resize: vertical;
        padding: 0.6rem 0.7rem;
        border: 1px solid #cbd7e2;
        border-radius: 8px;
        font: inherit;
        font-size: 0.9rem;
        background: #fbfdff;
    }
    .wf-dialog textarea:focus {
        outline: none;
        border-color: #289672;
        box-shadow: 0 0 0 3px rgba(40, 150, 114, 0.14);
        background: white;
    }
    .wf-error { color: #b42318; font-size: 0.82rem; margin-top: 0.4rem; display: none; }
    .wf-dialog-actions { display: flex; justify-content: flex-end; gap: 0.6rem; margin-top: 1.1rem; }
</style>

<div class="wf-backdrop" id="wfBackdrop" role="dialog" aria-modal="true" aria-labelledby="wfTitle">
    <div class="wf-dialog">
        <h2 id="wfTitle">Confirm</h2>
        <p class="wf-sub" id="wfSub"></p>

        <form method="post" id="wfForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" id="wfAction">

            <label for="wfRemark">Comment <span id="wfRemarkHint"></span></label>
            <textarea name="remark" id="wfRemark" placeholder="Add a note for the form history..."></textarea>
            <div class="wf-error" id="wfError">A reason is required to reject.</div>

            <div class="wf-dialog-actions">
                <button type="button" class="btn btn-secondary" id="wfCancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="wfConfirm">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const backdrop = document.getElementById('wfBackdrop');
        const form     = document.getElementById('wfForm');
        const title    = document.getElementById('wfTitle');
        const sub      = document.getElementById('wfSub');
        const actionIn = document.getElementById('wfAction');
        const remark   = document.getElementById('wfRemark');
        const hint     = document.getElementById('wfRemarkHint');
        const error    = document.getElementById('wfError');
        const confirm  = document.getElementById('wfConfirm');

        if (!backdrop) return;

        let remarkRequired = false;
        const base = <?= json_encode(rtrim(site_url('form/status'), '/')) ?>;

        function open(trigger) {
            const d = trigger.dataset;
            remarkRequired = d.wfRemarkRequired === '1';

            form.action     = base + '/' + d.wfFormId;
            actionIn.value  = d.wfAction;
            title.textContent = d.wfLabel;
            sub.textContent = d.wfFormName;
            remark.value    = '';
            error.style.display = 'none';

            hint.textContent = remarkRequired ? '(required)' : '(optional)';
            remark.placeholder = remarkRequired
                ? 'Explain what needs to change, so it can be corrected and resubmitted...'
                : 'Add a note for the form history...';
            confirm.className = 'btn ' + (remarkRequired ? 'btn-danger' : 'btn-primary');

            backdrop.setAttribute('open', '');
            remark.focus();
        }

        function close() {
            backdrop.removeAttribute('open');
        }

        document.querySelectorAll('[data-wf-trigger]').forEach(function (btn) {
            btn.addEventListener('click', function () { open(btn); });
        });

        document.getElementById('wfCancel').addEventListener('click', close);

        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) close();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && backdrop.hasAttribute('open')) close();
        });

        form.addEventListener('submit', function (e) {
            if (remarkRequired && remark.value.trim() === '') {
                e.preventDefault();
                error.style.display = 'block';
                remark.focus();
            }
        });
    })();
</script>
