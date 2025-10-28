<?php
// index.php - small form UI to choose gateway and enter details
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Payment Demo</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
  body { 
    background: #f5f6fa; 
  }

  

  /* Card styling */
  .card { 
    border-radius: 12px; 
    border: 2px solid #ddd; /* visible border */
    box-shadow: 0 8px 20px rgba(0,0,0,0.15); /* subtle shadow */
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .card:hover { 
    transform: translateY(-5px); 
    box-shadow: 0 12px 30px rgba(0,0,0,0.25); /* stronger shadow on hover */
  }

  /* Input fields */
  .form-control {
    border: 1.5px solid #ccc; /* visible border */
    border-radius: 8px;
    box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
    transition: border-color 0.2s, box-shadow 0.2s;
  }

  .form-control:focus {
    border-color: #0d6efd; /* bootstrap primary blue */
    box-shadow: 0 0 8px rgba(13, 110, 253, 0.25);
    outline: none;
  }

  /* Tabs styling */
  .nav-tabs .nav-link {
    cursor: pointer;
    border-radius: 8px 8px 0 0;
  }

  .nav-tabs .nav-link.active {
    background-color: #0d6efd;
    color: white;
    font-weight: 500;
  }

  /* Form sections */
  .form-section { 
    display: none; 
  }

  .form-section.active { 
    display: block; 
  }
</style>

</head>
<body class="bg-light">
<div class="container py-5">
  <h1 class="mb-4">Payment Demo</h1>

  <form action="process.php" method="post" class="card p-4">
    <div class="mb-3">
      <label class="form-label">Gateway</label>
      <select name="gateway" class="form-select" required>
        <option value="paypal">PayPal (sandbox)</option>
        <option value="visa">Visa (card)</option>
        <option value="mastercard">MasterCard (card)</option>
      </select>
    </div>

    <div class="row g-2 mb-3">
      <div class="col">
        <label class="form-label">Amount</label>
        <input name="amount" type="number" step="0.01" class="form-control" required value="10.00">
      </div>
      <div class="col">
        <label class="form-label">Currency</label>
        <input name="currency" type="text" class="form-control" required value="USD">
      </div>
    </div>

    <div id="cardFields">
      <h6>Card details (Visa / MasterCard)</h6>
      <div class="mb-2">
        <input name="cardNumber" type="text" class="form-control" placeholder="Card number (numbers only)">
      </div>
      <div class="row g-2">
        <div class="col">
          <input name="expiry" type="text" class="form-control" placeholder="MM/YY">
        </div>
        <div class="col">
          <input name="cvv" type="text" class="form-control" placeholder="CVV">
        </div>
      </div>
      <div class="mb-2 mt-2">
        <input name="cardHolder" type="text" class="form-control" placeholder="Card holder name">
      </div>
    </div>

    <div id="paypalFields" style="display:none;">
      <h6>PayPal sandbox credentials (test)</h6>
      <div class="mb-2"><input name="paypalClientId" class="form-control" placeholder="PayPal Client ID"></div>
      <div class="mb-2"><input name="paypalClientSecret" class="form-control" placeholder="PayPal Client Secret"></div>
    </div>

    <div class="mt-3">
      <button class="btn btn-primary">Pay</button>
      <a href="init_db.php" class="btn btn-outline-secondary" target="_blank"> DB</a>
      
    </div>
  </form>

  <script>
    const gateway = document.querySelector('select[name="gateway"]');
    const cardFields = document.getElementById('cardFields');
    const paypalFields = document.getElementById('paypalFields');
    function switchFields(){
      if(gateway.value === 'paypal'){ cardFields.style.display='none'; paypalFields.style.display='block'; }
      else { cardFields.style.display='block'; paypalFields.style.display='none'; }
    }
    gateway.addEventListener('change', switchFields);
    switchFields();
  </script>
</div>
</body>
</html>
