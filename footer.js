document.addEventListener("DOMContentLoaded", function() {
    const footer = document.createElement('footer');
    footer.innerHTML = `
        <p class="jalur_footer" style="text-align:center; padding: 2px 0; background-color: var(--secondary-light); margin: 0; position: relative;">
            <a href="javascript:void(0)" style="float:left; margin-left:20px; color: var(--primary); text-decoration: none;" onclick="showPageHelp()">
                <i class="fas fa-info-circle"></i> Info
            </a>
            &copy; 2024 JALUR GROUP
            <a href="javascript:void(0)" style="float:right; margin-right:20px; color: var(--primary); text-decoration: none;" onclick="showFeedbackPopup()">
                <i class="fas fa-comments"></i> Site Feedback
            </a>
        </p>
		
        <div id="feedbackPopup" class="popup" style="display:none; position:fixed !important; top:50%; left:50%; transform:translate(-50%, -50%); background-color:var(--dark); padding:20px; box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.5); border-radius:10px; z-index:99999; width:400px;">
            <form id="feedbackForm" onsubmit="submitFeedback(); return false;" style="display:flex; flex-direction:column; width:100%; box-sizing:border-box; margin-bottom:0px;">
                <label for="feedbackComment" class="feedback-label" style="margin-bottom:10px; font-size: 14px; color: var(--dark); text-align: center;">
                    <i class="fas fa-pencil-alt"></i> Bug? <br> UI issue? <br> Missing Functionality? <br> How can we improve?
                </label>
                <textarea id="feedbackComment" name="feedbackComment" required style="width:100%; height:100px; margin-bottom:15px; padding:10px; border:1px solid var(--secondary-light); border-radius:5px; box-sizing:border-box;"></textarea>
                <div class="button-container" style="display:flex; justify-content:space-between;">
				
                    <button type="button" onclick="closeFeedbackPopup()" style="background-color:var(--primary); color:white; border:none; padding:8px 12px; border-radius:5px; cursor:pointer; display:flex; align-items:center;">
                        <i class="fas fa-times-circle"></i>
                    </button>
					
                    <button type="submit" style="background-color:var(--primary); color:white; border:none; padding:8px 12px; border-radius:5px; cursor:pointer; display:flex; align-items:center;">
                        <i class="fas fa-paper-plane"></i>
                    </button>

                </div>
            </form>
            <div id="feedbackMessage" style="display:none; text-align:center; font-size:14px; color: var(--primary); margin-top:15px;">Thank you for your feedback!</div>
        </div>
		
        <div id="pageHelpPopup" class="popup" style="display:none; position:fixed !important; top:50%; left:50%; transform:translate(-50%, -50%); background-color:white; color:black; padding:20px; box-shadow:0px 8px 16px rgba(0,0,0,0.5); border-radius:10px; z-index:99999; width:90%; height:90%; overflow:auto;">
            <div id="pageHelpContent" style="font-size:14px; text-align:left;"></div>
            <button onclick="closePageHelpPopup()" style="background-color:var(--primary); color:white; border:none; padding:8px 12px; border-radius:5px; cursor:pointer;">Close</button>
        </div>
    `;
    document.body.appendChild(footer);
});

function showFeedbackPopup() {
    document.getElementById('feedbackPopup').style.display = 'block';
}

function closeFeedbackPopup() {
    document.getElementById('feedbackPopup').style.display = 'none';
}

function submitFeedback() {
    const comment = document.getElementById('feedbackComment').value;
    const contactID = localStorage.getItem('ContactID');
    const pageFilename = window.location.pathname.split('/').pop();

    const feedbackData = {
        contactID: contactID,
        pageFilename: pageFilename,
        comment: comment
    };

    postData('site_feedback_set.php', feedbackData)
    .then(response => {
        document.getElementById('feedbackForm').style.display = 'none';
        document.getElementById('feedbackMessage').style.display = 'block';
        setTimeout(() => {
            document.getElementById('feedbackPopup').style.display = 'none';
            document.getElementById('feedbackForm').style.display = 'block';
            document.getElementById('feedbackMessage').style.display = 'none';
            document.getElementById('feedbackComment').value = '';
        }, 1500);
    })
    .catch(error => {
        console.error('Error submitting feedback:', error);
    });
}

function showPageHelp() {
    const pageFilename = window.location.pathname.split('/').pop();

    postData('t_pagehelp_get.php', { pageFilename: pageFilename })
        .then(response => {
            if (response.success && response.data) {
                // document.getElementById('pageHelpContent').innerText = response.data.HelpContent || 'No help content available for this page.';
				document.getElementById('pageHelpContent').innerHTML = response.data.HelpContent || 'No help content available for this page.';
            } else {
                // If no data is found, or a message is provided
                document.getElementById('pageHelpContent').innerText = response.message || 'No help content available for this page.';
            }
            document.getElementById('pageHelpPopup').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching page help:', error);
            document.getElementById('pageHelpContent').innerText = 'Failed to load help content.';
        });
}

function closePageHelpPopup() {
    document.getElementById('pageHelpPopup').style.display = 'none';
}
