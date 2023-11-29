<?php
require_once (__DIR__.'/crest.php');?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){
            var table = $('<table>');
            $('#filter').on('input', function(){
                var value = $(this).val().toLowerCase();
                $('table tr').filter(function(){
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
</head>
<input type="text" id="filter" placeholder="Фильтр"style='margin-bottom: 15px;'>

<?php
$entityData = [];

for ($entity = 1; $entity <= 50; $entity++) {
    $result = CRest::call(
        'crm.timeline.comment.list',
        [
            'filter' => [
                'ENTITY_ID' => $entity,
                'ENTITY_TYPE' => 'deal',
              //  "CREATED"=>"2023-11-27",
            ],
            'select' => [
                'ID',
                'COMMENT',
                'CREATED',
                'ENTITY_ID',
                'AUTHOR_ID',
            ],
            'order' => [
                'ID' => 'DESC',
            ],
            'limit' => 1,
        ]
    );

    if ($result['error']) {
        echo "Ошибка: " . $result['error_description'];
    } else {
        $data = $result['result'];
        $commentCount = count($data);

        if ($commentCount > 0) {
            $lastComment = $data[0]['COMMENT'];
            $lastCommentDate = $data[0]['CREATED'];
            $lastCommentDateFormatted = date('d.m.Y', strtotime($lastCommentDate));
            $authorId = $data[0]['AUTHOR_ID'];

            $authorResult = CRest::call(
                'user.get',
                [
                    'ID' => $authorId,
                ]
            );

            if ($authorResult['error']) {
                echo "Ошибка: " . $authorResult['error_description'];
            } else {
                $authorData = $authorResult['result'][0];
                $authorName = $authorData['NAME'];
                $authorLastname = $authorData['LAST_NAME'];
                $authorFullName = $authorLastname . " " . $authorName;

                $entityData[$entity] = [
                    'commentCount' => $commentCount,
                    'lastComment' => $lastComment,
                    'lastCommentDate' => $lastCommentDateFormatted,
                    'authorFullName' => $authorFullName,
                ];
            }
        }
    }
}

echo "<table id='dataTable' class='table' style='text-align: center;'>";
echo "<tr><th style='border: 1px solid black;'>Номер сделки</th><th style='border: 1px solid black;'>Количество комментариев</th><th style='border: 1px solid black;'>Последний комментарий</th><th style='border: 1px solid black;'>Дата последнего комментария</th><th style='border: 1px solid black;'>Автор комментариев</th></tr>";
foreach ($entityData as $entityId => $entityInfo) {
    echo "<tr>";
    echo "<td style='border: 1px solid black;'>" . $entityId . "</td>";
    echo "<td style='border: 1px solid black;'>" . $entityInfo['commentCount'] . "</td>";
    echo "<td style='border: 1px solid black;'>" . $entityInfo['lastComment'] . "</td>";
    echo "<td style='border: 1px solid black;'>" . $entityInfo['lastCommentDate'] . "</td>";
    echo "<td style='border: 1px solid black;'>" . $entityInfo['authorFullName'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
