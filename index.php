<?php
include_once __DIR__ . '/config.php';
include_once __DIR__ . '/inc/constants.php';
include_once __DIR__ . '/inc/helpers.php';
include_once __DIR__ . '/inc/api.php';

$WS_DNS_API = new WS_DNS_API();

$page_title = 'Records list';
include_once __DIR__ . '/header.php';

$result = null;
$domain_name = DEFAULT_DOMAIN;
if (isset($_REQUEST['domain_name']) && !empty($_REQUEST['domain_name'])) {
	$domain_name = $_REQUEST['domain_name'];
}

try {
	$records = $WS_DNS_API->getAllRecords($domain_name);
} catch (Exception $e) {
	$records = $e;
}

if (isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'getRecords') {
	    $records = $WS_DNS_API->getAllRecords($domain_name);
    }

    if ($_REQUEST['action'] == 'addRecord') {
        $add_record_data = null;
        if (isset($_REQUEST['add_record']) && !empty($_REQUEST['add_record'])) {
            $add_record_data = (object) $_REQUEST['add_record'];
        }
	    try {
		    $result = $WS_DNS_API->addRecord($domain_name, $add_record_data);
	    } catch (Exception $e) {
		    $result = $e;
	    }

	    try {
		    $records = $WS_DNS_API->getAllRecords($domain_name);
	    } catch (Exception $e) {
		    $records = $e;
	    }
    }

    if (substr($_REQUEST['action'], 0, strlen('removeRecord-')) === 'removeRecord-') {
        $record_id = str_replace('removeRecord-', '', $_REQUEST['action']);
	    try {
		    $result = $WS_DNS_API->removeRecord($domain_name, $record_id);
	    } catch (Exception $e) {
            $result = $e;
	    }

	    try {
		    $records = $WS_DNS_API->getAllRecords($domain_name);
	    } catch (Exception $e) {
		    $records = $e;
	    }
    }
}
?>
<form method="post">
    <div class="domain-name-input-group">
        <label for="domain_name">Domain: </label>
        <input type="text" name="domain_name" id="domain_name" value="<?php echo $domain_name; ?>">
        <button type="submit" name="action" value="getRecords">List records</button>
    </div>
    <div class="add-record-input-group">
        <label for="add_record_type">Type: </label>
        <select name="add_record[type]" id="add_record_type">
            <option value="">Select type</option>
            <?php foreach(DNS_RECORD_TYPES as $type => $fields) {
                echo '<option value="' . $type . '">' . $type . '</option>';
            } ?>
        </select>
        <label for="add_record_name">Name: </label>
        <input type="text" name="add_record[name]" id="add_record_name" value="@" placeholder="Use @ for whole domain">
        <label for="add_record_content">Content: </label>
        <input type="text" name="add_record[content]" id="add_record_content"></input>
        <label for="add_record_ttl">TTL: </label>
        <input type="number" name="add_record[ttl]" id="add_record_ttl" value="600" placeholder="Time to live, default value is 600 seconds">
        <label for="add_record_priotity">Priority: </label>
        <input type="number" name="add_record[prio]" id="add_record_priotity">
        <label for="add_record_port">Port: </label>
        <input type="number" name="add_record[port]" id="add_record_port">
        <label for="add_record_weight">Weight: </label>
        <input type="number" name="add_record[weight]" id="add_record_weight">
        <label for="add_record_note">Note: </label>
        <input type="text" name="add_record[note]" id="add_record_note" placeholder="Optional note">
        <button type="submit" name="action" value="addRecord">Add record</button>
    </div>
	<?php
    if ($result !== null) {
        echo '<div class="request-result">';
            if ($result instanceof Exception) {
                echo '<span class="error">' . $result->getMessage() . '</span>';
            } else {
	            if (isJson($result) || isJson($result->response)) {
		            if (isset($result->response)) {
			            $result = json_decode($result->response);
		            } else {
			            $result = json_decode($result);
		            }
                    if (isset($result->error)) {
	                    echo '<span class="error">' . $result->error . '</span>';
                    } elseif (isset($result->message)) {
	                    echo '<span class="error">' . $result->message . '</span>';
                    } else {
	                    if (isset($result->messages) && !empty($result->messages)) {
                            foreach ($result->messages as $message) {
                                echo '<span class="message">' . $message . '</span>';
                            }
                        }
                        if (!empty($result->errors)) {
	                        foreach($result->errors as $key => $error_array) {
		                        $error = $error_array;
                                if (is_array($error_array)) {
	                                $error = implode(' ', $error_array);
                                }
                                echo '<div class="error">' . $key . ' - ' . $error . '</div>';
                            }
                        } else {
                            if (isset($result->status) && !empty($result->status)) {
                                if ($result->status === 'success') {
                                    echo '<span class="success">Request was successful</span>';
                                } else if ($result->status === 'error') {
	                                echo '<span class="error">Request was unsuccessful</span>';
                                } else {
	                                echo $result->status;
                                }
                            } else {
	                            print_r($result);
                            }
                        }
                    }
                } else {
                    if (!empty($result)) {
                        echo $result;
                    }
                }
            }
        echo '</div>';
    }
    ?>
    <table class="records-list">
        <?php
        if ($records instanceof Exception) {
	        echo '<span class="error">' . $records->getMessage() . '</span>';
        } else {
            if(isset($records->messages) && !empty($records->messages)) {
                foreach ($records->messages as $message) {
                    echo '<span class="message">' . $message . '</span>';
                }
            }
            if (isJson($records) || isJson($records->response)) {
                if (isset($records->response)) {
	                $records = json_decode($records->response);
                } else {
                    $records = json_decode($records);
                }
                if (isset($records->error)) {
	                echo '<span class="error">' . $records->error . '</span>';
                } elseif (isset($records->message)) {
	                echo '<span class="error">' . $records->message . '</span>';
                } else {
                    if (isset($records->items)) {
                        $records = $records->items;
                        if (is_array($records)) {
                            foreach ($records as $key => $record) {
                                echo '<tr>';
                                    echo '<td><span>Type:</span> <span>' . $record->type . '</span></td>';
                                    echo '<td><span>Name:</span> <span>' . $record->name . '</span></td>';
                                    echo '<td><span>Content:</span> <span>' . $record->content . '</span></td>';
                                    echo '<td><span>TTL:</span> <span>' . $record->ttl . '</span></td>';
                                    echo '<td><span>Priority:</span> <span>' . ($record->prio ?? null) . '</span></td>';
                                    echo '<td><span>Port:</span> <span>' . ($record->port ?? null) . '</span></td>';
                                    echo '<td><span>Weight:</span> <span>' . ($record->weight ?? null) . '</span></td>';
                                    echo '<td><span>Note:</span> <span>' . $record->note . '</span></td>';
                                    echo '<td><button type="submit" name="action" value="removeRecord-' . $record->id  . '">Remove record</button></td>';
                                echo '</tr>';
                            }
                        } else {
                            print_r($records);
                        }
                    } else {
                        print_r($records);
                    }
                }
            } else {
                if (!empty($records)) {
                    echo $records;
                }
            }
        }
        ?>
    </table>
</form>
<?php
include_once __DIR__ . '/footer.php';
?>
