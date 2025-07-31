function showMessage(severity, headline, detail, displayDurationSeconds = null, redirectUrl = null) {
	// Set default durations based on the message type if not provided
	if (!displayDurationSeconds) {
		switch (severity.toLowerCase()) {
			case 'information':
				displayDurationSeconds = 1;
				break;
			case 'warning':
				displayDurationSeconds = 2;
				break;
			case 'error':
				displayDurationSeconds = 4;
				break;
			case 'technical':
				displayDurationSeconds = 7;
				break;
			default:
				displayDurationSeconds = 3; // Default duration if not any of the above
		}
	}

	// Create the container for the message
	const messageContainer = document.createElement('div');
	messageContainer.style.position = 'fixed';
	messageContainer.style.top = '20%';
	messageContainer.style.left = '50%';
	messageContainer.style.transform = 'translate(-50%, -50%)';
	messageContainer.style.display = 'flex';
	messageContainer.style.alignItems = 'center';
	messageContainer.style.gap = '10px';
	messageContainer.style.padding = '20px';
	messageContainer.style.borderRadius = '8px';
	messageContainer.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
	messageContainer.style.backgroundColor = 'white';
	messageContainer.style.color = 'var(--erpt-dark)';
	messageContainer.style.width = '300px';
	messageContainer.style.opacity = '0';
	messageContainer.style.transition = 'opacity 0.5s ease';
	messageContainer.style.zIndex = '1000';

	const colorSettings = {
		'information': 'var(--erpt-primary-light)',
		'warning': 'var(--erpt-secondary-light)',
		'error': 'var(--primary)',
		'technical': '#f2c544',  // A distinct color for technical errors
		'default': 'var(--erpt-dark-light)'
	};

	const icon = getMessageIcon(severity, colorSettings[severity.toLowerCase()] || colorSettings['default']);

	messageContainer.innerHTML = `
		<div style="font-size: 32px; flex-shrink: 0;">${icon}</div>
		<div style="flex-grow: 1; max-width: 600px;">
			<h3 style="margin: 0 0 6px 0; font-size: 24px; font-weight: bold;">${headline}</h3>
			<p style="margin: 0; font-size: 18px;">${detail}</p>
		</div>
		<div style="position: absolute; bottom: 0; left: 0; height: 7px; background-color: ${colorSettings[severity.toLowerCase()] || colorSettings['default']}; width: 0%; transition: width ${displayDurationSeconds}s linear;"></div>
	`;

	document.body.appendChild(messageContainer);

	setTimeout(() => messageContainer.style.opacity = '1', 10);

	setTimeout(() => {
		messageContainer.style.opacity = '0';
		setTimeout(() => {
			document.body.removeChild(messageContainer);
			if (redirectUrl) {
				window.location.href = redirectUrl;
			}
		}, 500); // Wait for the fade out to complete
	}, displayDurationSeconds * 1000);
}

function getMessageIcon(severity, color) {
	const icons = {
		'information': `<i class="fas fa-info-circle" style="color: ${color};"></i>`,
		'warning': `<i class="fas fa-exclamation-triangle" style="color: ${color};"></i>`,
		'error': `<i class="fas fa-times-circle" style="color: ${color};"></i>`,
		'technical': `<i class="fas fa-bug" style="color: ${color};"></i>`, // Bug icon for technical errors
		'default': `<i class="fas fa-question-circle" style="color: ${color};"></i>`
	};
	return icons[severity.toLowerCase()] || icons['default'];
}

function showConfirmMessage(messageText) {
	return new Promise((resolve) => {
		// Overlay
		const overlay = document.createElement('div');
		overlay.style.position = 'fixed';
		overlay.style.top = '0';
		overlay.style.left = '0';
		overlay.style.width = '100vw';
		overlay.style.height = '100vh';
		overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.4)';
		overlay.style.zIndex = '1000';
		overlay.style.display = 'flex';
		overlay.style.alignItems = 'center';
		overlay.style.justifyContent = 'center';

		// Modal box
		const modal = document.createElement('div');
		modal.style.backgroundColor = 'white';
		modal.style.borderRadius = '8px';
		modal.style.padding = '20px';
		modal.style.boxShadow = '0 4px 12px rgba(0,0,0,0.3)';
		modal.style.maxWidth = '400px';
		modal.style.textAlign = 'center';

		// Message
		const message = document.createElement('p');
		message.innerHTML = messageText;
		message.style.marginBottom = '20px';
		message.style.fontSize = '18px';

		// Buttons
		const yesBtn = document.createElement('button');
		yesBtn.textContent = 'Yes';
		yesBtn.style.marginRight = '10px';
		yesBtn.style.padding = '8px 16px';
		yesBtn.style.border = 'none';
		yesBtn.style.backgroundColor = 'var(--erpt-primary-light)';
		yesBtn.style.color = '#fff';
		yesBtn.style.borderRadius = '4px';
		yesBtn.style.cursor = 'pointer';

		const noBtn = document.createElement('button');
		noBtn.textContent = 'No';
		noBtn.style.padding = '8px 16px';
		noBtn.style.border = 'none';
		noBtn.style.backgroundColor = '#ccc';
		noBtn.style.color = '#333';
		noBtn.style.borderRadius = '4px';
		noBtn.style.cursor = 'pointer';

		// Event handlers
		yesBtn.onclick = () => {
			document.body.removeChild(overlay);
			resolve(true);
		};
		noBtn.onclick = () => {
			document.body.removeChild(overlay);
			resolve(false);
		};

		// Assemble modal
		modal.appendChild(message);
		modal.appendChild(yesBtn);
		modal.appendChild(noBtn);
		overlay.appendChild(modal);
		document.body.appendChild(overlay);
	});
}


