function Maintain_Event(ObjectType, ObjectID, EventGroupID = null, EventID = null, EventNote = '', RecordStatus = '5', options = {}) {
    const statusMap = options.statusMap || {
        'D': { text: 'Deleted', color: 'darkgrey', DefaultStatus: '5' },
        'A': { text: 'Active', color: 'lightblue', DefaultStatus: '5' },
        '1': { text: '1. Not Started, Unassigned', color: 'red', DefaultStatus: '5' },
        '2': { text: '2. Assigned Not Overdue', color: 'yellow', DefaultStatus: '5' },
        '3': { text: '3. Assigned Overdue', color: 'orange', DefaultStatus: '5' },
        '4': { text: '4. WIP', color: 'lightgreen', DefaultStatus: '5' },
        '5': { text: '5. Complete', color: 'green', DefaultStatus: '5' },
        '6': { text: '6. Cancelled', color: 'lightred', DefaultStatus: '5' }
    };

    const assignEnabled = options.assignEnabled || false;

    postData('/eventgroup_get.php', { EventGroupID: EventGroupID })
    .then(response => {
        if (!response.success) {
            console.error('Error fetching event group data:', response.message);
            showMessage('Error', 'Failed to Fetch Data', 'There was an error fetching the event group data.', 2);
            return;
        }

        const eventGroupData = response.data.eventGroupDesc;
        const events = response.data.events;

        const initialPlaceholder = events.length > 0 ? events[0].PlaceHolder : '';
        const defaultPlannedDate = new Date();
        defaultPlannedDate.setDate(defaultPlannedDate.getDate() + 1);

        const popupForm = document.createElement('div');
        popupForm.innerHTML = `
            <div id="popupOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 9999;">
                <div id="popupForm" style="background: #fff; padding: 20px; border-radius: 8px; width: 400px;">
                    <h3>${eventGroupData}</h3>
                    <p>Log Event for ${ObjectType}: ${ObjectID}</p>
                    <label for="eventSelector">Select Event:</label>
                    <select id="eventSelector" required style="width: 100%;">
                        ${events.map(event => `<option value="${event.EventID}" data-placeholder="${event.PlaceHolder}" ${event.EventID === EventID ? 'selected' : ''}>${event.EventID} - ${event.EventDesc}</option>`).join('')}
                    </select>
                    <br><br>
                    <label for="eventNote">Event Note:</label>
                    <textarea id="eventNote" rows="4" style="width: 100%;" placeholder="${initialPlaceholder}">${EventNote}</textarea>
                    <br><br>
                    <label for="recordStatusSelector">Select Status:</label>
                    <select id="recordStatusSelector" style="width: 100%;">
                        ${Object.keys(statusMap).map(key => `<option value="${key}" ${key === RecordStatus ? 'selected' : ''}>${statusMap[key].text}</option>`).join('')}
                    </select>
                    <br><br>
                    <button id="assignButton">Assign</button>
                    <div id="assignFields" style="display: ${assignEnabled ? 'block' : 'none'};">
                        <br>
                        <label for="assignedTo">Assigned To:</label>
                        <input type="text" id="assignedTo" class="universalID-input" data-universalid-type="RE" placeholder="Enter Universal ID or Search by Name" onclick="initializeUniversalIDSearch(this)" value="${options.AssignedTo || ''}">
                        <br><br>
                        <label for="plannedExecutionDate">Planned Execution Date:</label>
                        <input type="date" id="plannedExecutionDate" style="width: 100%;" value="${options.PlannedExecutionDate || defaultPlannedDate.toISOString().substr(0, 10)}">
                    </div>
                    <br>
                    <button id="cancelButton">Cancel</button>
                    <button id="deleteButton">Delete</button>
                    <button id="saveButton">Save</button>
                </div>
            </div>
        `;

        document.body.appendChild(popupForm);

        document.getElementById('assignButton').addEventListener('click', function() {
            const assignFields = document.getElementById('assignFields');
            assignFields.style.display = assignFields.style.display === 'none' ? 'block' : 'none';
        });

        document.getElementById('eventSelector').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('eventNote').placeholder = selectedOption.getAttribute('data-placeholder') || '';
        });

        document.getElementById('cancelButton').addEventListener('click', () => {
            document.body.removeChild(popupForm);
            showMessage('Information', 'Cancelled', 'Event creation/editing was cancelled.', 1);
        });

        document.getElementById('deleteButton').addEventListener('click', () => {
            Log_Event(ObjectType, ObjectID, document.getElementById('eventSelector').value, document.getElementById('eventNote').value, 'D');
            document.body.removeChild(popupForm);
            showMessage('Information', 'Deleted', 'Event was successfully deleted.', 1);
        });

        document.getElementById('saveButton').addEventListener('click', () => {
            Log_Event(
                ObjectType,
                ObjectID,
                document.getElementById('eventSelector').value,
                document.getElementById('eventNote').value,
                document.getElementById('recordStatusSelector').value,
                null,
                document.getElementById('assignedTo').value,
                document.getElementById('plannedExecutionDate').value
            );
            document.body.removeChild(popupForm);
            showMessage('Information', 'Success!', 'Event Saved', 1);
        });
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'Failed to Load Data', 'There was an error while loading data for the event.', 2);
    });
}

function Log_Event(ObjectType, ObjectID, EventID, EventNote, RecordStatus = 'A', EventGroupID = null, AssignedTo = null, PlannedExecutionDate = null) {
    if (!jsnvalidate(ObjectType) || !jsnvalidate(ObjectID) || !jsnvalidate(EventID)) {
        console.error('Invalid input parameters');
        return;
    }

    const eventData = {
        ObjectType: ObjectType,
        ObjectID: ObjectID,
        EventID: EventID,
        EventNote: EventNote,
        RecordStatus: RecordStatus,
        AssignedTo: AssignedTo || localStorage.getItem('ContactID'),
        PlannedExecutionDate: PlannedExecutionDate || new Date().toISOString().split('T')[0]
    };

    postData('/eventlogs_set.php', eventData)
        .then(response => {
            if (response.success) {
                console.log('Event logged successfully');
                // showMessage('Information', 'Success!', 'Event has been logged successfully.', 5);
                // showMessage('Information', 'Success!', 'Data has been stored and your Tasklist has been updated!', 3);
            } else {
                console.error('Error logging event:', response.message);
                showMessage('Error', 'Failed', 'There was an issue logging the event.', 2);
            }
        })
        .catch(error => {
            console.error('Error in postData:', error);
            showMessage('Error', 'Failed', 'An error occurred while logging the event.', 2);
        });
}

function jsnvalidate(value) {
    return typeof value !== 'undefined' && value !== null;
}

async function autoLogEvent(EventID, NextPageURL) {
    const myResourceID = await ContactID_get_ResourceID();
    if (myResourceID !== 'Fail') {
        Log_Event('ResourceID', myResourceID, EventID, 'Auto Logged', '5');
		showMessage('Information', 'Success!', 'Your data was saved and your Tasklist has been updated!', 2, NextPageURL);
    } else {
        showMessage('Error', 'Failed to Log Event', 'ResourceID could not be retrieved.', 2);
    }
}

async function ContactID_get_ResourceID() {
    try {
        const data = await postData('/Get_ResourceID_from_ContactID.php');
        if (data.success && data.ResourceID) {
            return data.ResourceID;
        } else {
            console.error('Failed to retrieve ResourceID:', data.message);
            return 'Fail';
        }
    } catch (error) {
        console.error('Error:', error);
        return 'Fail';
    }
}
