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
        $status_text = isset($row["state"]) && $row["state"] == "1" ? 'ON' : 'OFF';

        $html_buttons .= '
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="card-title text-truncate mb-0">' . sanitize($row["name"]) . '</h5>
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger ms-2" 
                                onclick="deleteOutput(' . $row["id"] . ')"
                                title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    
                   <div class="mb-4">
                        <div class="d-flex flex-column gap-2 mb-2">
                            <span class="badge badge-primary mt-2">Board ' . sanitize($row["board"]) . '</span>
                            <span class="badge badge-info mt-2">GPIO ' . sanitize($row["gpio"]) . '</span>
                            <span class="badge badge-secondary mt-2">' . strtoupper($row["type"]) . '</span>
                        </div>
                    </div>

                    <div class="mt-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex gap-2">';

                    if ($row["type"] == 'output') {
                        $html_buttons .= '
                                <div class="custom-control custom-switch custom-switch-lg">
                                    <input type="checkbox" class="custom-control-input" 
                                        id="switch-' . $row["id"] . '" 
                                        onchange="updateOutput(' . $row["id"] . ')" 
                                        ' . $button_checked . '>
                                    <label class="custom-control-label" for="switch-' . $row["id"] . '"></label>
                                </div>';
                    }

                    $html_buttons .= '
                                <div class="status-indicator">
                                    <span id="status-badge-' . $row["id"] . '" class="badge badge-' . $color . ' status-badge">
                                        <i id="status-icon-' . $row["id"] . '" class="fas fa-circle me-1 ' . ($row["state"] ? 'text-success' : 'text-danger') . '"></i>
                                        <span id="status-text-' . $row["id"] . '" class="status-text">' . $status_text . '</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
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
        </tr>
        ';
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
        .status-indicator .badge {
            padding: 0.5em 0.75em;
            border-radius: 20px;
        }
        .status-indicator .fa-circle {
            font-size: 0.8em;
            margin-right: 5px;
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
        <!-- STATUS BOARD -->
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

        <!-- GPIO CONTROLS -->
        <div class="row mb-4">
            <div class="col">
                <h4 class="mb-3"><i class="fas fa-sliders-h"></i> GPIO Controls</h4>
                <div class="row" id="outputs-container">
                    <?php 
                        // Card-card hasil looping ditampilkan di sini
                        echo $html_buttons; 
                    ?>
                </div>
            </div>
        </div>

        <!-- FORM CREATE NEW OUTPUT -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
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
                            <div class="form-group">
                                <label>Jenis GPIO</label>
                                <select class="form-control" id="outputType">
                                    <option value="output">Output</option>
                                    <option value="input">Input</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Create
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        function updateOutput(id) {
            const checkbox = $(`#switch-${id}`);
            const state = checkbox.prop('checked') ? 1 : 0;
            
            // Find only the status badge within this card
            const statusBadge = checkbox.closest('.card-body').find('.status-badge');
            const statusIcon = checkbox.closest('.card-body').find('.status-badge i');
            const statusText = checkbox.closest('.card-body').find('.status-text');
            
            // Add loading state
            statusBadge.addClass('opacity-50');
            
            $.ajax({
                url: `esp-outputs-action.php?action=output_update&id=${id}&state=${state}`,
                success: function() {
                    // Remove loading state
                    statusBadge.removeClass('opacity-50');
                    
                    // Update badge color
                    statusBadge.removeClass('badge-success badge-danger')
                            .addClass(state ? 'badge-success' : 'badge-danger');
                    
                    // Update icon color
                    statusIcon.removeClass('text-success text-danger')
                            .addClass(state ? 'text-success' : 'text-danger');
                    
                    // Update status text
                    statusText.text(state ? 'ON' : 'OFF');
                },
                error: function() {
                    // Remove loading state and revert checkbox on error
                    statusBadge.removeClass('opacity-50');
                    checkbox.prop('checked', !state);
                    alert('Failed to update output. Please try again.');
                }
            });
        }

        function deleteOutput(id) {
            if (confirm('Are you sure you want to delete this output?')) {
                const card = $(`#switch-${id}`).closest('.col-md-6');
                card.addClass('opacity-50');
                
                $.ajax({
                    url: `esp-outputs-action.php?action=output_delete&id=${id}`,
                    success: function(response) {
                        card.fadeOut(300, function() {
                            $(this).remove();
                        });
                    },
                    error: function() {
                        card.removeClass('opacity-50');
                        alert('Failed to delete output. Please try again.');
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
                state: $('#outputState').val(),
                type: $('#outputType').val()
            };
            $.ajax({
                url: 'esp-outputs-action.php',
                method: 'POST',
                data: {
                    action: 'output_create',
                    ...formData
                },
                success: function(response) {
                    alert(response); // Tampilkan respon server
                    location.reload();
                }
            });
        }

        // Auto-refresh setiap 1 detik
        function updateStatusElements(id, state) {
            const badge = $(`#status-badge-${id}`);
            const icon = $(`#status-icon-${id}`);
            const text = $(`#status-text-${id}`);
            
            // Update classes
            badge.removeClass('badge-success badge-danger')
                .addClass(state ? 'badge-success' : 'badge-danger');
            
            icon.removeClass('text-success text-danger')
                .addClass(state ? 'text-success' : 'text-danger');
            
            // Update text
            text.text(state ? 'ON' : 'OFF');
        }

        setInterval(function() {
            $.ajax({
                url: 'fetch.php',
                dataType: 'json',
                success: function(data) {
                    data.forEach(function(output) {
                        const checkbox = $(`#switch-${output.id}`);
                        const currentState = checkbox.prop('checked');
                        
                        // Update checkbox jika berbeda
                        if (currentState !== (output.state === 1)) {
                            checkbox.prop('checked', output.state === 1);
                        }
                        
                        // Update status elements
                        updateStatusElements(output.id, output.state);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching states:', error);
                }
            });
        }, 1000);

    </script>
</body>
</html>
