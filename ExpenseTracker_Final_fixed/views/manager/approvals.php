<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../models/approvalModel.php';

$manager_id = $_SESSION['user_id'] ?? 0;
$from_date = $_GET['from_date'] ?? null;
$to_date = $_GET['to_date'] ?? null;

// Fetch all employee requests for the logged-in manager
$expenses = getTeamExpensesWithFilter($manager_id, $from_date, $to_date);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Expenses - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .page-title { font-size: 2rem; color: #ffffff; margin-bottom: 25px; font-weight: normal; }
        
        .filter-form { display: flex; gap: 15px; margin-bottom: 25px; align-items: center; }
        .filter-form input[type="date"] {
            padding: 10px 14px; background: #121620; border: 1px solid #1c2230;
            color: #a0a8b9; border-radius: 4px; font-size: 0.95rem;
        }
        .btn-search {
            padding: 10px 30px; background: #121620; border: 1px solid #1c2230;
            color: #ffffff; border-radius: 4px; cursor: pointer; font-size: 1rem;
        }
        .btn-search:hover { background: #1c2230; }

        .expense-table { width: 100%; border-collapse: collapse; background: #121620; border-radius: 6px; overflow: hidden; }
        .expense-table th, .expense-table td { padding: 16px 20px; border-bottom: 1px solid #1c2230; color: #ffffff; }
        .expense-table th { text-align: left; font-weight: 600; }
        
        .status-pending { color: #f59e0b; font-weight: bold; }
        .status-approved { color: #10b981; font-weight: bold; }
        .status-rejected { color: #ef4444; font-weight: bold; }

        .btn-view { color: #60a5fa; text-decoration: none; font-weight: 500; }
        .btn-view:hover { text-decoration: underline; }
        
        .btn-action-approve { padding: 6px 14px; background: #e5c185; border: none; color: #000; 
        border-radius: 4px;  cursor: pointer; font-weight: bold; margin-right: 6px; }
        .btn-action-reject { padding: 6px 14px; background: #283042; border: none; color: #fff; border-radius: 4px; cursor: pointer; }

        /* Modal styling */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.75); justify-content: center; align-items: center; z-index: 1000; }
        .modal-box { background: #121620; padding: 30px; border-radius: 8px; width: 440px; color: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.5); }
        .modal-title { font-size: 1.6rem; color: #e5c185; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; color: #a0a8b9; font-size: 0.9rem; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px; background: #1c2230; border: 1px solid #283042; color: #ffffff; border-radius: 4px; box-sizing: border-box; }
        
        .btn-confirm-reject { padding: 10px 20px; background: #e56b6f; border: none; color: #ffffff; border-radius: 4px; cursor: pointer; font-weight: bold; flex: 1; }
        .btn-cancel-modal { padding: 10px 20px; background: #8c939d; border: none; color: #000000; border-radius: 4px; cursor: pointer; font-weight: bold; flex: 1; }
        
        .alert-message { padding: 12px 15px; border-radius: 4px; margin-bottom: 20px; font-size: 0.95rem; }
        .alert-success { background-color: #1e3a29; color: #4ade80; border: 1px solid #22543d; }
        .alert-error { background-color: #3b1c1c; color: #f87171; border: 1px solid #5c2424; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <h1 class="page-title">Employee Expenses</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-message alert-success"><?= htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert-message alert-error"><?= htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form method="GET" class="filter-form">
            <input type="date" name="from_date" value="<?= htmlspecialchars($from_date ?? ''); ?>" placeholder="From (mm/dd/yyyy)">
            <input type="date" name="to_date" value="<?= htmlspecialchars($to_date ?? ''); ?>" placeholder="To (mm/dd/yyyy)">
            <button type="submit" class="btn-search">Search</button>
        </form>

        <table class="expense-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($expenses && mysqli_num_rows($expenses) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($expenses)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['expense_date']); ?></td>
                            <td><?= htmlspecialchars($row['user_name']); ?></td>
                            <td><?= htmlspecialchars($row['category_name']); ?></td>
                            <td><?= number_format($row['expense_amount']); ?> Tk</td>
                            <td class="status-<?= strtolower($row['expense_status']); ?>">
                                <?= htmlspecialchars($row['expense_status']); ?>
                            </td>
                            <td>
                                <?php if ($row['expense_status'] === 'Pending'): ?>
                                    <button type="button" onclick='openApproveModal(<?= json_encode($row); ?>)' class="btn-action-approve">Approve</button>
                                    <button type="button" onclick='openRejectModal(<?= json_encode($row); ?>)' class="btn-action-reject">Reject</button>
                                <?php else: ?>
                                    <a href="employeeViewExpense.php?id=<?= $row['expense_id']; ?>" class="btn-view">View</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color:#a0a8b9;">No employee expenses found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal 1: Approve Expense Confirmation Modal -->
    <div id="approveModal" class="modal-overlay">
        <div class="modal-box">
            <h2 class="modal-title">Approve Expense</h2>
            <form action="../../controllers/approvalcontroller.php" method="POST">
                <input type="hidden" name="expense_id" id="approve_expense_id">
                
                <div class="form-group">
                    <label>Employee name</label>
                    <input type="text" id="approve_user_name" class="form-control" readonly>
                </div>

                <div class="modal-grid">
                    <div class="form-group">
                        <label>Expense Title</label>
                        <input type="text" id="approve_title" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="text" id="approve_date" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" id="approve_category" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="text" id="approve_amount" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="approve_description" class="form-control" readonly style="height:60px; resize:none;"></textarea>
                </div>

                <div style="display:flex; gap:15px; margin-top:20px;">
                    <button type="submit" name="approve_expense" class="btn-action-approve" style="flex:1; padding:10px; margin:0;">Approve</button>
                    <button type="button" onclick="closeApproveModalAndOpenReject()" class="btn-action-reject" style="flex:1; padding:10px;">Reject</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Rejection Reason Input Modal -->
    <div id="rejectModal" class="modal-overlay">
        <div class="modal-box">
            <h2 class="modal-title" style="color:#f87171; text-align:left; margin-bottom:10px;">Rejected</h2>
            <p id="reject_summary" style="color:#d1d5db; margin-bottom:20px; font-size:0.95rem;"></p>
            
            <form action="../../controllers/approvalcontroller.php" method="POST">
                <input type="hidden" name="expense_id" id="reject_expense_id">
                
                <div class="form-group">
                    <label>Rejection Reason</label>
                    <textarea name="reason" class="form-control" placeholder="Explain why are you rejecting" required style="height:90px; resize:none;"></textarea>
                </div>

                <div style="display:flex; gap:15px; margin-top:25px;">
                    <button type="submit" name="reject_expense" class="btn-confirm-reject">Confirm</button>
                    <button type="button" onclick="closeModals()" class="btn-cancel-modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentSelectedData = null;

        function openApproveModal(data) {
            currentSelectedData = data;
            document.getElementById('approve_expense_id').value = data.expense_id;
            document.getElementById('approve_user_name').value = data.user_name;
            document.getElementById('approve_title').value = data.expense_title;
            document.getElementById('approve_date').value = data.expense_date;
            document.getElementById('approve_category').value = data.category_name;
            document.getElementById('approve_amount').value = data.expense_amount + " Tk";
            document.getElementById('approve_description').value = data.expense_description ?? '';
            document.getElementById('approveModal').style.display = 'flex';
        }

        function openRejectModal(data) {
            currentSelectedData = data;
            document.getElementById('reject_expense_id').value = data.expense_id;
            document.getElementById('reject_summary').innerText = data.user_name + ", " + data.category_name + ", " + data.expense_amount + " tk, " + data.expense_date;
            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeApproveModalAndOpenReject() {
            document.getElementById('approveModal').style.display = 'none';
            if (currentSelectedData) {
                openRejectModal(currentSelectedData);
            }
        }

        function closeModals() {
            document.getElementById('approveModal').style.display = 'none';
            document.getElementById('rejectModal').style.display = 'none';
        }
    </script>
</body>
</html>