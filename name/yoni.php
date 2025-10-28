<!DOCTYPE html>
<html>
<head>
    <title>Letter Y with PHP</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            text-align: center;
            padding: 20px;
            background-color: #f5f5f5;
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
            font-size: 25px;
        }
    </style>
</head>
<body>
    <h2>Displaying Letter Y with Astrics</h2>
    
    <div class="letter-container">
        <?php
        $size = 7;
        
        for ($i = 0; $i < $size; $i++) {
            for ($j = 0; $j < $size; $j++) {
                if (
                    ($j == $i && $i < $size / 2) || 
                    ($j == $size - 1 - $i && $i < $size / 2) || 
                    ($j == floor($size / 2) && $i >= $size / 2)
                ) {
                    echo "<span class='asterisk'>*</span>";
                } else {
                    echo "&nbsp;&nbsp;";
                }
            }
            echo "<br>";
        }
        ?>
    </div>
</body>
</html>
