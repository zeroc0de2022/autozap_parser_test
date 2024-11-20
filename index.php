<?php
declare(strict_types = 1);

use DiDom\Exceptions\InvalidSelectorException;

require_once __DIR__ . '/vendor/autoload.php';

// Ключевое слово для поиска
if(isset($_REQUEST['keyword'])){
    $keyword = $_REQUEST['keyword'];
    // Запуск парсера
    $parser = new Parser\Extractor($keyword);
    try {
        $content = $parser->loadPage();
        $parser->printPre(json_encode($content, JSON_UNESCAPED_UNICODE), true, true);
    }
    catch(InvalidSelectorException $exception) {
        $parser->printPre($exception->getMessage());
    }
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">


<header class="p-3 text-bg-dark">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <input type="search" class="form-control me-2" placeholder="Search..." aria-label="Search">
        </div>
    </div>
</header>
<div class="container-fluid pb-12">
    <div class="d-grid col-12 mx-auto" style="grid-template-columns: 1fr 2fr;">
        <div class="bg-body-tertiary border rounded-6 p-5" id="json">

        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('input').addEventListener('keydown', function(e) {
            var keyword = document.querySelector('input').value;
            if(e.key === 'Enter') {
                document.getElementById('json').innerHTML = '';
                fetch('?keyword=' + keyword)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('json').innerHTML = data;
                    });
            }
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>