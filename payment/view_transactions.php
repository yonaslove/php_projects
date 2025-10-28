<?php
$dbPath = __DIR__ . '/data/payments.sqlite';

// Connect to DB
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch all records
$stmt = $db->query("SELECT * FROM payments ORDER BY created_at DESC");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transactions Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f6fa; padding: 20px; }
        h1 { text-align: center; color: #222; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 14px;
            text-align: center;
        }
        th {
            background: #007bff;
            color: #fff;
            text-transform: uppercase;
        }
        tr:nth-child(even) { background: #ececec; }
        tr:hover { background: #d8e6ff; cursor: pointer; }
        a.back {
            text-decoration: none;
            background: #007bff;
            padding: 10px 18px;
            color: #fff;
            border-radius: 6px;
            display: inline-block;
            margin-top: 20px;
        }
        .status-success {
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>

    <h1>Transaction Records 💳</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Method</th>
            <th>Amount</th>
            <th>Currency</th>
            <th>Status</th>
            <th>Transaction ID</th>
            <th>Created</th>
        </tr>
        <?php foreach ($transactions as $t): ?>
        <tr>
            <td><?= $t['id'] ?></td>
            <td><?= htmlspecialchars($t['method']) ?></td>
            <td><?= number_format($t['amount'], 2) ?></td>
            <td><?= $t['currency'] ?></td>
            <td class="status-success"><?= $t['status'] ?></td>
            <td><?= htmlspecialchars($t['transaction_id']) ?></td>
            <td><?= $t['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <center>
        <a href="index.php" class="back">← Back to Payment</a>
    </center>

</body>
</html>
