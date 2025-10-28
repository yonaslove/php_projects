<!DOCTYPE html>
<html>
<head>
    <title>Letter Y Pattern Generator</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            text-align: center;
            padding: 20px;
            background-color: #f5f5f5;
        }
        form {
            margin-bottom: 20px;
        }
        input[type=number] {
            padding: 8px;
            font-size: 16px;
            width: 80px;
            text-align: center;
        }
        input[type=submit] {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor:pointer;
        }
        input[type=submit]:hover {
            background-color: #0056b3;
        }
        .letter-container {
            display: inline-block;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin: 10px;
        }
        .asterisk {
            color: blueviolet;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <h2>Generate Letter Y Pattern</h2>

    <form method="post">
        <label>Enter Size (odd number ≥ 5): </label>
        <input type="number" name="size" min="5" step="2" required>
        <input type="submit" value="Show Pattern">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $size = intval($_POST["size"]);

        echo "<div class='letter-container'>";
        echo "<h3>Asterisk Pattern (Size: $size)</h3>";

        for ($i = 0; $i < $size; $i++) {
            for ($j = 0; $j < $size; $j++) {
                if (
                    ($j == $i && $i < $size / 2) || 
                    ($j == $size - 1 - $i && $i < $size / 2) || 
                    ($j == floor($size / 2) && $i >= $size / 2)
                ) {
                    echo "<span class='asterisk'>* </span>";
                } else {
                    echo "&nbsp;&nbsp;";
                }
            }
            echo "<br>";
        }

        echo "</div>";
    }
    ?>
</body>
</html>
