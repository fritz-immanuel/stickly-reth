function parseJSONString(input) {
    try {
        if (Array.isArray(input)) {
            return input;
        }
        if (typeof input !== 'string') {
            console.error('Input is not a string:', input);
            return [];
        }
        // Decode escaped characters
        input = input.replace(/\\"/g, '"').replace(/\\\\/g, '\\');
        // Remove any extra quotes around the string
        input = input.replace(/^"|"$/g, '');
        // Parse the JSON string
        return JSON.parse(input);
    } catch (e) {
        console.error('Error parsing JSON string:', e);
        return [];
    }
}

function JSONtoMySQLOK(jsonObj) {
    if (typeof jsonObj !== 'object' || jsonObj === null) {
        console.error('Invalid object:', jsonObj);
        return '';
    }
    try {
        // Convert JSON object to string
        let jsonString = JSON.stringify(jsonObj);
        // Escape single quotes for SQL compatibility
        return jsonString.replace(/'/g, "\\'");
    } catch (e) {
        console.error('Error converting JSON to MySQL string:', e);
        return '';
    }
}

function showDataInPopup(data, variableName) {
    // Convert data to a formatted JSON string
    const jsonData = JSON.stringify(data, null, 2);

    // Create the initial line
    const initialLine = `Here is the data within the ${variableName} variable:\n\n`;

    // Combine the initial line with the JSON data
    const textContent = initialLine + jsonData;

    // Create a container div for the popup
    const popupContainer = document.createElement('div');
    popupContainer.style.position = 'fixed';
    popupContainer.style.top = '50%';
    popupContainer.style.left = '50%';
    popupContainer.style.transform = 'translate(-50%, -50%)';
    popupContainer.style.padding = '20px';
    popupContainer.style.backgroundColor = 'white';
    popupContainer.style.border = '1px solid black';
    popupContainer.style.zIndex = '1000';

    // Create a textarea element
    const textarea = document.createElement('textarea');
    textarea.value = textContent;
    textarea.style.width = '400px';
    textarea.style.height = '300px';
    textarea.id = 'dataTextarea';

    // Append the textarea to the popup container
    popupContainer.appendChild(textarea);

    // Create a button to copy the text to the clipboard
    const copyButton = document.createElement('button');
    copyButton.textContent = 'Copy';
    copyButton.style.marginTop = '10px';
    copyButton.onclick = () => {
        textarea.select();
        document.execCommand('copy');
        // alert('Text copied to clipboard');
    };

    // Append the copy button to the popup container
    popupContainer.appendChild(copyButton);

    // Create a button to close the popup
    const closeButton = document.createElement('button');
    closeButton.textContent = 'Close';
    closeButton.style.marginTop = '10px';
    closeButton.style.marginLeft = '10px';
    closeButton.onclick = () => document.body.removeChild(popupContainer);

    // Append the close button to the popup container
    popupContainer.appendChild(closeButton);

    // Append the popup container to the body
    document.body.appendChild(popupContainer);
}
