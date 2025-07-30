<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block gptassistant is defined here.
 *
 * @package     block_gptassistant
 * @copyright   ChatGPT Assistant
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_gptassistant extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_gptassistant');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        global $CFG, $PAGE, $OUTPUT;

        require_once(__DIR__.'/../../config.php');
        require_once($CFG->dirroot.'/mod/vpl/locallib.php');
        require_once($CFG->dirroot.'/mod/vpl/vpl.class.php');
        require_once($CFG->dirroot.'/mod/vpl/vpl_submission_CE.class.php');
        require_once($CFG->dirroot.'/mod/vpl/views/sh_factory.class.php');
	require_login();

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $PAGE->requires->css(new moodle_url('/blocks/gptassistant/css/style.css'));
       /*  $PAGE->requires->js(new moodle_url('/blocks/gptassistant/js/script.js', array('v' => time()))); */

        //module name Is it necessary TBD?
        //$modulenameplural = get_string('modulenameplural', 'mod_chatgptassistant');
        //$this->content->text .= $OUTPUT->heading($modulenameplural);

        // barebone php rendering uncomment for old button 
        //$this->content->text .= '<form method="post"><button type="submit" name="get_hint">Get Hint</button></form>';
        //begin HTML CSS JS


        // function to be changed by Florian
        $hint = "";
        if (isset($_POST['get_hint'])) {
            // Your VPL settings
	    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $vpl = new mod_vpl($id);
            $userid = isset($_GET['userid']) ? intval($_GET['userid']) : 0;

            $subinstance = $vpl->last_user_submission($userid);
            $submission = new mod_vpl_submission_CE($vpl, $subinstance);
            $result = $submission->print_ce(true);
            $description = $vpl->get_fulldescription();


            if ($submission) {
            //    $this->content->text .= "Comments: " . $result . "<br>";
            //    $this->content->text .= "Description: " . $description . "<br>";
            //    $this->content->text .= "Files submitted: ";
                $files = $submission->get_submitted_files();
            //    foreach ($files as $file) {
            //        $this->content->text .= $file . "<br>";
            }
            //} else {
            //    $this->content->text .= "No submission found for the user.";
            //}


            $openaiApiKey = "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";
            $apiUrl = "https://api.openai.com/v1/chat/completions";


            $data = array(
                "model" => "gpt-4-turbo-preview",
                "messages" => array(
                    array(
                        "role" => "system",
                        "content" => "You are a helpful assistant, Provide a brief hint under 60 words but don't give the answer to the solution . No direct answers, just guidance."
                    ),
                    array(
                        "role" => "user",
                        "content" => "Exercise: ".$description."\nChecker Output: ".$result."\nStudent Code:\n".implode("\n", $files)."\nHint:"
                    )
                )
            );

            $dataJson = json_encode($data);


            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $dataJson,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Bearer $openaiApiKey"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $hint = "cURL Error #:" . $err;
            } else {
                $responseArr = json_decode($response, true);
                $hint = $responseArr['choices'][0]['message']['content'];
            }
            $hint = '<div id="hintContainer" class="bot-message">'.nl2br($hint).'</div>';
        }
	$this->content->text .= '

            <!DOCTYPE html>
            <html>
            <head>
                <title>ChatGPT Assistant</title>
            </head>
            <body>
                <!-- Chatbox Container -->
                <div id="chatbox" class="chatbox-container">
                    <!-- Chatbox Header -->
                    <div class="chatbox-header">
                        <div class="chatbox-header-content">
                            <!-- Logo -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 116 116" height="30" width="30">
                            <path d="m107.697 47.468-.022-.026c4.684 5.189 7.537 12.065 7.537 19.607 0 5.376-1.449 10.413-3.904 14.604l-.074.139c-4.042 6.984-10.918 12.123-19.244 13.878l-.059.207c-3.842 11.685-14.842 20.122-27.813 20.122-.009 0-.019 0-.026 0h.008c-.056 0-.112 0-.167 0-8.594 0-16.312-3.747-21.636-9.725l.194-.036c-2.038.449-4.156.687-6.329.687-10.786 0-20.209-5.839-25.354-14.667l.074-.133c-2.558-4.312-4.025-9.345-4.025-14.721 0-3.273.544-6.419 1.486-9.149l.021.024c-4.707-5.188-7.576-12.074-7.576-19.632 0-5.413 1.473-10.482 3.964-14.693l.074-.141c4.041-6.986 10.92-12.127 19.249-13.877l.061-.207c3.894-11.62 14.87-19.994 27.802-19.994 8.644 0 16.413 3.741 21.801 9.718l-.194.036c2.035-.449 4.15-.686 6.32-.686 10.781 0 20.199 5.839 25.339 14.666l-.073-.133c2.566 4.319 4.04 9.364 4.04 14.753-.001 3.259-.54 6.393-1.474 9.113zm-43.603 60.942c11.982-.027 21.69-9.735 21.718-21.721v-26.984-.002c-.018-.108-.087-.2-.184-.25l-9.764-5.645v32.562c-.007 1.396-.758 2.614-1.895 3.291l-23.098 13.33-.682.392-.034-.027c3.76 3.154 8.607 5.054 13.899 5.054h.035zm-46.694-19.941.056.102c3.777 6.435 10.77 10.754 18.77 10.754 3.995 0 7.738-1.076 10.852-2.9l23.395-13.489v-.001c.093-.068.154-.176.158-.299v-11.273l-28.242 16.283.018-.01c-.557.331-1.207.521-1.902.521-.694 0-1.345-.19-1.885-.512l-23.119-13.333-.687-.412.021-.132c-.228 1.255-.346 2.548-.346 3.868 0 3.99 1.081 7.726 2.911 10.833zm-6.089-50.422.056-.104c-1.875 3.216-2.948 6.956-2.948 10.947 0 8.005 4.319 15 10.858 18.838l23.345 13.466-.002-.001c.052.027.11.043.174.043.062 0 .121-.016.171-.042l9.764-5.646-28.107-16.215-.019-.01c-1.11-.64-1.857-1.838-1.857-3.211 0-.016 0-.033 0-.047v-27.438l-.15.048c-4.74 1.74-8.704 5.09-11.285 9.488zm80.219 18.749.02.01c1.129.635 1.904 1.829 1.947 3.214v27.443l.148-.05c8.181-3.115 13.992-11.032 13.992-20.306 0-8.003-4.328-14.995-10.873-18.818l-23.345-13.489h.002c-.051-.027-.11-.043-.174-.043-.062 0-.121.016-.171.042l-9.739 5.626zm9.715-14.703-.02.131c.218-1.235.332-2.506.332-3.804 0-12.012-9.738-21.75-21.75-21.75-3.994 0-7.736 1.076-10.85 2.899l-23.344 13.471h-.001c-.083.058-.137.153-.137.262 0 .012.001.023.002.033v11.275l28.217-16.283-.017.01c.56-.334 1.215-.525 1.914-.525s1.354.191 1.897.516l23.074 13.446.682.411v-.092zm-61.074-12.412v-.002c.009-1.395.762-2.614 1.899-3.289l23.094-13.331.687-.387.034.028c-3.772-3.15-8.631-5.046-13.931-5.046-11.997 0-21.726 9.714-21.75 21.707v26.907.002c.018.115.086.215.184.273l9.764 5.626zm5.307 35.553 12.601 7.25 12.552-7.25v-14.495l-12.601-7.25-12.576 7.25z"/>
                            </svg>
                            <!-- Title -->
                            <div class="chatbox-title">ChatGPT Assistant</div>
                            <!-- Online Indicator -->
                            <div class="chatbox-status">
                            <svg height="15" width="15" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="8" cy="8" r="8" fill="green" />
                            </svg>
                            <span>Online</span>
                            </div>
                        </div>
                    </div>
                    <!-- Chatbox Body -->
                    <div class="chatbox-body">
                        <div class="chatbox-messages" id="chatbox-messages">
                            <!-- Static Bot Message First Hint -->
                            <div class="message bot-message">
                            <span >Hello you have only <b>3 hints!</b> How can I assist you today?</span>
                            </div>
                        </div>
                        <div class="message user-message">
                            <form method="post"><button  id="getHintNow" type="submit" name="get_hint">Get hint now!</button></form>
                        </div>
                        <div id="message-container" class="message">
                            <div class="message bot-message" id="loadingIndicator" style="display:none;">...</div>
                            <div id="message-content"> <span >'.$hint.'</span></div>
                        </div>
                    <!-- Chatbox Footer -->
                    <div class="chatbox-footer fix-bottom">
                            <div class="input-container">
                            <input class="chat-input" id="chat-input" placeholder="Type your message..." readonly>
                            <button class="send-button" disabled>
                                <svg height="20" viewBox="0 0 116 116" width="20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="m13.66 1.583c-7.024-3.625-11.328-.453-9.516 7.477l7.477 33.305c.906 3.852 4.758 7.477 8.836 7.93l44.859 5.211c12.008 1.359 12.008 3.625 0 4.984l-44.859 5.21c-4.078.453-7.93 3.852-8.836 7.703l-7.477 33.758c-1.812 7.703 2.492 10.875 9.516 7.25l93.343-49.617c7.023-3.852 7.023-9.742 0-13.593z" fill="#476CFF" stroke="white" stroke-width="5"/>
                                </svg>
                            </button>
                            </div>
                    </div>
                </div>
            </body>
            </html>
          
    ';
                
        //end HTML CSS JS

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {

        // Load user-defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_gptassistant');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return [
            'admin' => false,
            'site-index' => true,
            'course-view' => true,
            'mod-vpl-*' => true,
            'my' => true,
        ];
    }
}
