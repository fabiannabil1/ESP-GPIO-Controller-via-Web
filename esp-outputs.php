<?php
include_once 'esp-database.php';

// Fungsi keamanan tambahan dengan penanganan nilai null
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data ?? '')));
}

$result = getAllOutputs();
$html_buttons = '';
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $button_checked = isset($row["state"]) && $row["state"] == "1" ? "checked" : "";
        $color = isset($row["state"]) && $row["state"] == "1" ? "success" : "danger";
        
        // Pastikan 'last_request' memiliki nilai, jika tidak gunakan string kosong
        $last_request = $row["last_request"] ?? '';
        $last_active = (strtotime($last_request) > time() - 30) ? "active" : "inactive";

        $html_buttons .= '
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">'.sanitize($row["name"]).'</h5>
                            <p class="card-subtitle mb-2 text-muted">
                                Board '.sanitize($row["board"]).' - GPIO '.sanitize($row["gpio"]).'
                            </p>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteOutput('.$row["id"].')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="switch-'.$row["id"].'" 
                                onchange="updateOutput('.$row["id"].')" '.$button_checked.'>
                            <label class="custom-control-label" for="switch-'.$row["id"].'"></label>
                        </div>
                        <span class="badge badge-'.$color.'">
                            '.(isset($row["state"]) && $row["state"] == "1" ? 'ON' : 'OFF').'
                        </span>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    Last update: '.sanitize($last_request).'
                    <span class="status-indicator '.$last_active.'"></span>
                </div>
            </div>
        </div>';
    }
}

$result2 = getAllBoards();
$html_boards = '';
if ($result2) {
    while ($row = $result2->fetch_assoc()) {
        $last_request = $row["last_request"] ?? '';
        $status = (strtotime($last_request) > time() - 30) ? "Online" : "Offline";
        $badge = $status == "Online" ? "success" : "secondary";
        
        $html_boards .= '
        <tr>
            <td>Board '.sanitize($row["board"]).'</td>
            <td>'.sanitize($last_request).'</td>
            <td><span class="badge badge-'.$badge.'">'.$status.'</span></td>
        </tr>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT GPIO Controller</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4a76a8;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
        }
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .status-indicator {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-left: 8px;
        }
        .status-indicator.active {
            background-color: var(--success);
        }
        .status-indicator.inactive {
            background-color: var(--secondary);
        }
        .custom-switch {
            padding-left: 3.5rem;
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-microchip"></i> IoT Controller
            </a>
        </div>
    </nav>
    <div class="container">
        <div class="row mb-4">
            <div class="col">
                <div class="alert alert-info">
                    <h4 class="alert-heading"><i class="fas fa-info-circle"></i> System Status</h4>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <?php echo $html_boards; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col">
                <h4 class="mb-3"><i class="fas fa-sliders-h"></i> GPIO Controls</h4>
                <div class="row" id="outputs-container">
                    <?php echo $html_buttons; ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"><i class="fas fa-plus-circle"></i> Create New Output</h4>
                        <form id="createForm" onsubmit="return createOutput(event)">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" class="form-control" id="outputName" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Board ID</label>
                                    <input type="number" class="form-control" id="outputBoard" min="0" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>GPIO Number</label>
                                    <input type="number" class="form-control" id="outputGpio" min="0" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Initial State</label>
                                <select class="form-control" id="outputState">
                                    <option value="0">OFF</option>
                                    <option value="1">ON</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Create Output
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        function updateOutput(id) {
            const checkbox = $(`#switch-${id}`);
            const state = checkbox.prop('checked') ? 1 : 0;
            
            $.ajax({
                url: `esp-outputs-action.php?action=output_update&id=${id}&state=${state}`,
                success: function() {
                    const badge = $(checkbox).closest('.card-body').find('.badge');
                    badge.removeClass('badge-success badge-danger')
                         .addClass(state ? 'badge-success' : 'badge-danger')
                         .text(state ? 'ON' : 'OFF');
                }
            });
        }
        function deleteOutput(id) {
            if(confirm('Are you sure you want to delete this output?')) {
                $.ajax({
                    url: `esp-outputs-action.php?action=output_delete&id=${id}`,
                    success: function() {
                        $(`#switch-${id}`).closest('.col-md-4').remove();
                    }
                });
            }
        }
        function createOutput(e) {
            e.preventDefault();
            const formData = {
                name: $('#outputName').val(),
                board: $('#outputBoard').val(),
                gpio: $('#outputGpio').val(),
                state: $('#outputState').val()
            };
            $.ajax({
                url: 'esp-outputs-action.php',
                method: 'POST',
                data: {
                    action: 'output_create',
                    ...formData
                },
                success: function(response) {
                    location.reload(); // Reload halaman setelah output dibuat
                }
            });
        }
        // Auto-refresh setiap 1 detik
        setInterval(function() {
            $.getJSON('fetch.php', function(data) {
                data.forEach(output => {
                    const checkbox = $(`#switch-${output.id}`);
                    const currentState = checkbox.prop('checked');
                    if (currentState != output.state) {
                        checkbox.prop('checked', output.state);
                        const badge = checkbox.closest('.card-body').find('.badge');
                        badge.removeClass('badge-success badge-danger')
                             .addClass(output.state ? 'badge-success' : 'badge-danger')
                             .text(output.state ? 'ON' : 'OFF');
                    }
                });
            });
        }, 1000);
    </script>
</body>
</html>
