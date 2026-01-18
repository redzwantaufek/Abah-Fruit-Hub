<?php
session_start();
require_once('db_conn.php');
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php');

// Dapatkan nama staff dari session
$current_staff_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Unknown Staff';

$msg = "";

if (isset($_POST['checkout'])) {
    $cust_id   = $_POST['cust_id'];
    $staff_id  = $_SESSION['user_id'];
    $total_amt = $_POST['final_total'];
    $pay_type  = $_POST['pay_method']; 
    $items     = $_POST['items']; 
    $qtys      = $_POST['qtys'];   

    if (empty($items)) {
        $msg = "Swal.fire('Error', 'Cart is empty!', 'error');";
    } else {
        $q_ord = "INSERT INTO ORDERS (OrderId, CustId, StaffId, OrderDate, TotalAmount, PaymentMethod, OrderStatus) 
                  VALUES (order_id_seq.NEXTVAL, :cid, :sid, SYSDATE, :amt, :pm, 'COMPLETED') 
                  RETURNING OrderId INTO :new_oid";
                  
        $stmt = oci_parse($dbconn, $q_ord);
        oci_bind_by_name($stmt, ":cid", $cust_id);
        oci_bind_by_name($stmt, ":sid", $staff_id);
        oci_bind_by_name($stmt, ":amt", $total_amt);
        oci_bind_by_name($stmt, ":pm", $pay_type); 
        oci_bind_by_name($stmt, ":new_oid", $order_id, -1, SQLT_INT);

        if (oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
            $success = true;
            
            for ($i = 0; $i < count($items); $i++) {
                $fid = $items[$i];
                $qty = $qtys[$i];

                $q_det = "INSERT INTO ORDERDETAILS (OrderDetailsId, OrderId, FruitId, Quantity) 
                          VALUES (orderdtl_id_seq.NEXTVAL, :oid, :fid, :qty)";
                $s_det = oci_parse($dbconn, $q_det);
                oci_bind_by_name($s_det, ":oid", $order_id);
                oci_bind_by_name($s_det, ":fid", $fid);
                oci_bind_by_name($s_det, ":qty", $qty);
                if (!oci_execute($s_det, OCI_NO_AUTO_COMMIT)) $success = false;

                $q_stk = "UPDATE FRUITS SET QuantityStock = QuantityStock - :qty WHERE FruitId = :fid";
                $s_stk = oci_parse($dbconn, $q_stk);
                oci_bind_by_name($s_stk, ":qty", $qty);
                oci_bind_by_name($s_stk, ":fid", $fid);
                if (!oci_execute($s_stk, OCI_NO_AUTO_COMMIT)) $success = false;
            }

            if ($success) {
                oci_commit($dbconn);
                $msg = "Swal.fire('Success', 'Transaction Completed!', 'success').then(() => { window.location = 'order_details.php?id=$order_id'; });";
            } else {
                oci_rollback($dbconn);
                $msg = "Swal.fire('Error', 'Transaction failed.', 'error');";
            }
        } else {
            $e = oci_error($stmt);
            $msg = "Swal.fire('Error', '" . $e['message'] . "', 'error');";
        }
    }
}
?>
<script><?php echo $msg; ?></script>

<div class="container-fluid">
    <div class="row h-100">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold text-white text-shadow mb-0"><i class="fas fa-boxes me-2"></i>Select Items</h4>
                <input type="text" id="searchFruit" class="form-control w-50 rounded-pill border-0 shadow-sm" placeholder="Search fruit name...">
            </div>

            <div class="row g-3" id="fruitGrid" style="max-height: 80vh; overflow-y: auto;">
                <?php
                $q = "SELECT * FROM FRUITS WHERE QuantityStock > 0 ORDER BY FruitName";
                $s = oci_parse($dbconn, $q);
                oci_execute($s);
                while ($f = oci_fetch_assoc($s)) {
                ?>
                <div class="col-md-3 col-6 fruit-card" data-name="<?php echo strtolower($f['FRUITNAME']); ?>">
                    <div class="glass-card p-3 h-100 text-center position-relative btn-add-cart" 
                         style="cursor: pointer; transition: transform 0.2s;"
                         onclick="addToCart(<?php echo $f['FRUITID']; ?>, '<?php echo htmlspecialchars($f['FRUITNAME']); ?>', <?php echo $f['FRUITPRICE']; ?>, <?php echo $f['QUANTITYSTOCK']; ?>)">
                        
                        <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2 shadow-sm" style="width:60px; height:60px;">
                            <i class="fas fa-apple-alt fa-2x text-success"></i>
                        </div>
                        <h6 class="fw-bold mb-1 text-truncate"><?php echo $f['FRUITNAME']; ?></h6>
                        <span class="badge bg-primary rounded-pill">RM <?php echo number_format($f['FRUITPRICE'], 2); ?></span>
                        <small class="d-block text-muted mt-1">Stock: <?php echo $f['QUANTITYSTOCK']; ?></small>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card p-4 h-100 d-flex flex-column" style="min-height: 85vh;">
                
                <div class="border-bottom pb-3 mb-3">
                    <h4 class="fw-bold mb-1"><i class="fas fa-shopping-cart me-2"></i>Current Sale</h4>
                    <div class="d-flex align-items-center text-primary mt-2 p-2 bg-light rounded">
                        <i class="fas fa-user-circle me-2 fs-5"></i>
                        <div>
                            <small class="text-muted d-block" style="line-height:1;">Cashier / Handler</small>
                            <span class="fw-bold"><?php echo htmlspecialchars($current_staff_name); ?></span>
                        </div>
                    </div>
                </div>
                
                <form method="POST" id="posForm" class="flex-grow-1 d-flex flex-column">
                    <div class="mb-3">
                        <label class="small fw-bold">Customer</label>
                        <select name="cust_id" class="form-select select2" required>
                            <?php
                            $c = oci_parse($dbconn, "SELECT CustId, CustName FROM CUSTOMER ORDER BY CustName");
                            oci_execute($c);
                            while ($r = oci_fetch_assoc($c)) {
                                echo "<option value='".$r['CUSTID']."'>".$r['CUSTNAME']."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="flex-grow-1 overflow-auto mb-3 pe-2" id="cartItems" style="max-height: 350px;">
                        <p class="text-muted text-center mt-5" id="emptyCartMsg">Cart is empty</p>
                    </div>

                    <div class="mt-auto border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Total Items:</span>
                            <span id="totalCount">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <h4 class="fw-bold">Grand Total:</h4>
                            <h4 class="fw-bold text-primary" id="grandTotal">RM 0.00</h4>
                            <input type="hidden" name="final_total" id="inputTotal" value="0">
                        </div>
                        
                        <div class="mb-3">
                            <label class="small fw-bold">Payment Method</label>
                            <select name="pay_method" class="form-select fw-bold border-primary">
                                <option value="CASH">💵 Cash</option>
                                <option value="QR">📱 QR Pay / DuitNow</option>
                                <option value="CARD">💳 Debit / Credit Card</option>
                            </select>
                        </div>

                        <button type="submit" name="checkout" class="btn btn-success w-100 fw-bold py-3 shadow rounded-pill mb-2">
                            <i class="fas fa-check-circle me-2"></i> COMPLETE SALE
                        </button>
                        <button type="button" onclick="clearCart()" class="btn btn-danger w-100 fw-bold shadow-sm rounded-pill">
                            Clear Cart
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap-5' });
    
    $("#searchFruit").on("keyup", function() {
        var val = $(this).val().toLowerCase();
        $(".fruit-card").filter(function() {
            $(this).toggle($(this).data("name").indexOf(val) > -1)
        });
    });
});

let cart = {};

function addToCart(id, name, price, stock) {
    if (cart[id]) {
        if (cart[id].qty < stock) {
            cart[id].qty++;
        } else {
            Swal.fire('Stock Limit', 'Not enough stock available.', 'warning');
        }
    } else {
        cart[id] = { name: name, price: price, qty: 1, stock: stock };
    }
    renderCart();
}

function renderCart() {
    let html = '';
    let total = 0;
    let count = 0;
    let isEmpty = true;

    for (let id in cart) {
        isEmpty = false;
        let item = cart[id];
        let sub = item.qty * item.price;
        total += sub;
        count += item.qty;

        html += `
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded bg-white shadow-sm">
            <div style="flex:1">
                <h6 class="mb-0 fw-bold">${item.name}</h6>
                <small class="text-muted">RM ${item.price.toFixed(2)} x ${item.qty}</small>
            </div>
            <div class="text-end">
                <span class="fw-bold d-block text-primary">RM ${sub.toFixed(2)}</span>
                <button type="button" onclick="remOne(${id})" class="btn btn-sm btn-outline-danger px-2 py-0 ms-1"><i class="fas fa-minus"></i></button>
            </div>
            <input type="hidden" name="items[]" value="${id}">
            <input type="hidden" name="qtys[]" value="${item.qty}">
        </div>`;
    }

    if (isEmpty) {
        $('#cartItems').html('<p class="text-muted text-center mt-5">Cart is empty</p>');
    } else {
        $('#cartItems').html(html);
    }

    $('#totalCount').text(count);
    $('#grandTotal').text('RM ' + total.toFixed(2));
    $('#inputTotal').val(total);
}

function remOne(id) {
    if (cart[id]) {
        cart[id].qty--;
        if (cart[id].qty <= 0) delete cart[id];
        renderCart();
    }
}

function clearCart() {
    cart = {};
    renderCart();
}
</script>
</body>
</html>