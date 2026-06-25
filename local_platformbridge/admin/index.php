<?php
require_once('../../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/platformbridge/admin/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_platformbridge'));
$PAGE->set_heading(get_string('pluginname', 'local_platformbridge'));

// Retrieve API settings
$apiurl = get_config('local_platformbridge', 'apiurl');
$apikey = get_config('local_platformbridge', 'apikey');
$orgid = get_config('local_platformbridge', 'orgid');

echo $OUTPUT->header();

// Display the UI elements
echo '<div id="platformbridge-admin">';
echo '  <h2>' . get_string('checkapistatus', 'local_platformbridge') . '</h2>';
echo '  <p id="api-status">Loading API status...</p>';
echo '  <h2>' . get_string('availablevms', 'local_platformbridge') . '</h2>';
echo '  <ul id="vm-list"></ul>';
echo '  <h2>' . get_string('availablelabs', 'local_platformbridge') . '</h2>';
echo '  <ul id="lab-list"></ul>';
echo '</div>';

// Embedded JavaScript for AJAX requests
echo '<script>
    const apiurl = "' . $apiurl . '";
    const apikey = "' . $apikey . '";
    const orgid = "' . $orgid . '";

    // Function to check API status
    async function checkApiStatus() {
        try {
            const response = await fetch(apiurl + "/integration/status?org_id=" + orgid + "&api_key=" + apikey, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json"
                }
            });
            const data = await response.json();
            document.getElementById("api-status").textContent = "API Status: " + data.status;
        } catch (error) {
            console.error("Error checking API status:", error);
            document.getElementById("api-status").textContent = "Error checking API status. Please try again later.";
        }
    }

    // Function to fetch available VMs
    async function fetchAvailableVMs() {
        try {
            const response = await fetch(apiurl + "/integration/vms/status?org_id=" + orgid + "&api_key=" + apikey, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json"
                }
            });
            const data = await response.json();
            const vmList = document.getElementById("vm-list");
            vmList.innerHTML = "";
            if (data.length > 0) {
                data.forEach(vm => {
                    vmList.innerHTML += `<li>${vm.name} (ID: ${vm.id}) - Status: ${vm.status}</li>`;
                });
            } else {
                vmList.innerHTML = "<li>No VMs available.</li>";
            }
        } catch (error) {
            console.error("Error fetching VMs:", error);
            document.getElementById("vm-list").innerHTML = "<li>Error fetching VMs. Please try again later.</li>";
        }
    }

    // Function to fetch available labs
    async function fetchAvailableLabs() {
        try {
            const response = await fetch(apiurl + "/integration/labs/status?org_id=" + orgid + "&api_key=" + apikey, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json"
                }
            });
            const data = await response.json();
            const labList = document.getElementById("lab-list");
            labList.innerHTML = "";
            if (data.length > 0) {
                data.forEach(lab => {
                    labList.innerHTML += `<li>${lab.name} (ID: ${lab.id}) - URL: ${lab.url}</li>`;
                });
            } else {
                labList.innerHTML = "<li>No labs available.</li>";
            }
        } catch (error) {
            console.error("Error fetching labs:", error);
            document.getElementById("lab-list").innerHTML = "<li>Error fetching labs. Please try again later.</li>";
        }
    }

    // Initialize the page
    checkApiStatus();
    fetchAvailableVMs();
    fetchAvailableLabs();
</script>';

echo $OUTPUT->footer();