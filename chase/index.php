<!DOCTYPE html>
<html>
<head>
    <title>PHP Chessboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f5f5f5;
            padding: 20px;
        }
        table {
            border-collapse: collapse;
            margin: 20px auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        td {
            width: 60px;
            height: 60px;
        }
        .white {
            background-color: #f0e6c6;
        }
        .blue {
            background-color: #5d4037;
        }
        h2 {
            color: #003366;
        }
    </style>
</head>
<body>

<h2>Chess Board using PHP</h2>

<table border="1">
    <?php
    for ($row = 1; $row <= 8; $row++) {
        echo "<tr>";
        for ($col = 1; $col <= 8; $col++) {
            $total = $row + $col;
            if ($total % 2 == 0) {
                
                echo "<td class='white'></td>";
            } else {
                echo "<td class='blue'></td>";
            }
        }
        echo "</tr>";
    }
    ?>
</table>

</body>
</html>