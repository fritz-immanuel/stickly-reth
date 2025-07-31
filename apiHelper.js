/**
 * Function to make POST requests and handle JSON data.
 * @param {string} url - The PHP file name to call.
 * @param {Object} body - The body data to send as JSON.
 * @param {string} [debugMode] - Set to 'ALL' for all debug output or 'ERROR_ONLY' for error-only debug output.
 * @param {string} [ok_url] - URL to redirect if response indicates success.
 * @returns {Promise<Object>} - A promise that resolves to the response data.
 */
function postData(url, body = {}, debugMode, ok_url) {
    // Set ContactID and EncrypKey from localStorage if not provided
    debugMode = 'ERROR_ONLY';  // Change this value as needed ('ALL' or 'ERROR_ONLY')
    const contactID = localStorage.getItem('ContactID');
    const encrypKey = localStorage.getItem('EncrypKey');
    if (!body.ContactID) {
        body.ContactID = contactID;
    }
    if (!body.EncrypKey) {
        body.EncrypKey = encrypKey;
    }

    if (debugMode === 'ALL') {
        console.log('URL:', url);
        console.log('Request Body:', JSON.stringify(body, null, 2));
    }

    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(body)
    })
    .then(response => {
        if (debugMode === 'ALL') {
            console.log('Response:', response);
        }
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                if (debugMode === 'ALL' || debugMode === 'ERROR_ONLY') {
                    console.warn('Expected JSON, received text:', text);
                    showMessage('technical', 'Technical Error', `PHP responded in text format (not JSON). Here is the text: ${text}`, 10);
                }
                // Attempt to parse text as JSON
                try {
                    return JSON.parse(text);
                } catch (error) {
                    if (debugMode === 'ALL' || debugMode === 'ERROR_ONLY') {
                        console.error('Failed to parse text as JSON:', text);
						showMessage('technical', 'Technical Error', `Error: The PHP is not returning JSON. Received: ${text}`, 10);
                    }
                    throw new Error(`Unexpected response format: ${text}`);
                }
            });
        }
    })
    .then(data => {
        if (debugMode === 'ALL') {
            console.log('Response Data:', JSON.stringify(data, null, 2));
        }

		if (data.redirect) {
			// The auth check PHP responded with redirect due to auth check failure
			window.location.href = data.redirect;
			return; // This ensures no further code is executed
		}

        if (ok_url && data.success) {
            window.location.href = ok_url;
        }
        return data;
    })
    .catch(error => {
        if (debugMode === 'ALL' || debugMode === 'ERROR_ONLY') {
            console.error('Error:', error);
			showMessage('technical', 'Technical Error', error, 10);
        }
        throw error;
    });
}

function postFormData(url, formData, debugMode = 'ERROR_ONLY', ok_url = null) {
    // Auto-append ContactID and EncryptionKey if not already in formData
    if (!formData.has('ContactID')) {
        const contactID = localStorage.getItem('ContactID');
        if (contactID) formData.append('ContactID', contactID);
    }
    if (!formData.has('EncryptionKey')) {
        const encrypKey = localStorage.getItem('EncrypKey');
        if (encrypKey) formData.append('EncryptionKey', encrypKey);
    }

    return fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                if (debugMode === 'ALL' || debugMode === 'ERROR_ONLY') {
                    console.warn('Expected JSON, received text:', text);
                    showMessage('technical', 'Technical Error', 'Non-JSON response: ${text}', 10);
                }

                try {
                    return JSON.parse(text);
                } catch {
                    throw new Error('Unexpected response: ${text}');
                }
            });
        }
    })
    .then(data => {
        if (data.redirect) {
            window.location.href = data.redirect;
            return;
        }

        if (ok_url && data.success) {
            window.location.href = ok_url;
        }
        return data;
    })
    .catch(error => {
        console.error('File upload error:', error);
        showMessage('technical', 'Upload Error', error.message, 10);
        throw error;
    });
}

// Toggle the spinner like this.
// toggle_Spinner(true);
// toggle_Spinner(false);
function toggle_Spinner(show) {
    let spinner = document.getElementById('spinner-container');

    if (show) {
        //Create spinner container if it doesn't exist
        if (!spinner) {
            spinner = document.createElement('div');
            spinner.id = 'spinner-container';
            document.body.appendChild(spinner);

            //Create spinning ring (doughnut)
            let ring = document.createElement('div');
            ring.style.position = "absolute";
            ring.style.width = "120px"; // Double size
            ring.style.height = "120px";
            ring.style.border = "12px solid var(--secondary)"; // Outer ring
            ring.style.borderTop = "12px solid var(--primary)"; // Spinning part
            ring.style.borderRadius = "50%";
            ring.style.animation = "spin 1s linear infinite";

            //Create static text in the center
            let text = document.createElement('div');
            text.innerText = "Loading...";
            text.style.position = "absolute";
            text.style.fontSize = "18px"; // Adjust as needed
            text.style.fontWeight = "bold";
            text.style.color = "var(--dark)";
            text.style.textAlign = "center";

            //Apply styles to spinner container (ensures full centering)
            spinner.style.position = "fixed";
            spinner.style.top = "50%";
            spinner.style.left = "50%";
            spinner.style.transform = "translate(-50%, -50%)";
            spinner.style.width = "120px";
            spinner.style.height = "120px";
            spinner.style.display = "flex";
            spinner.style.alignItems = "center";
            spinner.style.justifyContent = "center";
            spinner.style.zIndex = "1000";

            //Make sure text is inside the ring
            spinner.appendChild(ring);
            spinner.appendChild(text);

            //Add keyframes dynamically (only once)
            if (!document.getElementById('spinner-style')) {
                const styleSheet = document.createElement("style");
                styleSheet.id = "spinner-style";
                styleSheet.innerHTML = `
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                `;
                document.head.appendChild(styleSheet);
            }
        }
        spinner.style.display = "flex";
    } else {
        //Hide and remove spinner when done
        if (spinner) {
            spinner.style.display = "none";
            spinner.remove();
        }
    }
}


// ========== Periodic New Message Checker ==========
function startAlertPolling(intervalSeconds = 20) {
	setInterval(() => {
		postData('alert_check.php', { mode: 'check_alerts' })
			.then(res => {
				if (res.success && Array.isArray(res.data) && res.data.length > 0) {
					const alert = res.data[0];

					new Audio('/sounds/notify.mp3').play().catch(() => {});

					showActionMessage(
						'information',
						alert.MessageTitle,
						alert.MessageText,
						'View',
						() => window.location.href = alert.MessageURL
					);

					postData('alert_check.php', {
						mode: 'mark_read',
						MsgNo: alert.MsgNo
					});
				}
			})
			.catch(err => {
				console.error("Error polling for alerts:", err);
			});
	}, intervalSeconds * 1000);
}

// Immediately start alert polling
startAlertPolling();
