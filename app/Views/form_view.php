<!DOCTYPE html>
<html>
<head>
    <title><?= esc($form['name']) ?></title>
    <style>
        .inline-input {
            width: 80px;
            margin: 0 5px;
            display: inline-block;
        }
    </style>
</head>
<body>

<h2><?= esc($form['name']) ?></h2>



<?php foreach ($sections as $section): ?>
    <form method="post" action="http://localhost:8888/code4/public/index.php/form/submit">

<?= csrf_field() ?>
    <h3><?= esc($section['title']) ?></h3>

    <input type="hidden" name="table_name" value="<?= esc($section['table']) ?>">

    
    <?php if ($section['layout'] === 'inline'): ?>

        <?php
            $template = $section['inline_template'];

            // Map fields
            $fieldMap = [];
            foreach ($section['fields'] as $field) {
                $fieldMap[$field['name']] = $field;
            }

            // Replace placeholders safely
            $template = preg_replace_callback('/\{(.*?)\}/', function($matches) use ($fieldMap, $section,$values) {

                $name = $matches[1];

                if (!isset($fieldMap[$name])) {
                    return $matches[0];
                }

                $field = $fieldMap[$name];
                $validation = json_decode($field['validation'], true);


                $value = old('sections.'.$section['id'].'.'.$field['name']) 
            ?? ($values[$section['id']][$field['name']] ?? '');

            // $value='';
                // 
                // print_r($values);
// exit();



                // ✅ SINGLE LINE INPUT (IMPORTANT FIX)
                return '<input type="'.$field['type'].'"  value="'.esc($value).'"  name="sections['.$section['id'].']['.$field['name'].']" class="inline-input" '.(!empty($validation['required']) ? 'required' : '').'>';
            }, $template);

            // Preserve line breaks
            echo nl2br($template);
        ?>

    <?php else: ?>

        <?php foreach ($section['fields'] as $field): ?>

            <div>
                <label><?= esc($field['label']) ?></label>
                <input 
                    type="<?= esc($field['type']) ?>"
                    name="sections[<?= $section['id'] ?>][<?= $field['name'] ?>]"
                >
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

<?php endforeach; ?>

<br><br>
<button type="submit">Submit</button>

</form>

</body>
</html>