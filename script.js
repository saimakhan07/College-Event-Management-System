(() => {
  // Tabs logic
  const tabs = {
    admin: document.getElementById('tab-admin'),
    student: document.getElementById('tab-student'),
    panels: {
      admin: document.getElementById('panel-admin'),
      student: document.getElementById('panel-student')
    }
  };

  function activateTab(tabName) {
    Object.entries(tabs).forEach(([key, el]) => {
      if (key === tabName) {
        el.classList.add('active');
        el.setAttribute('aria-selected', 'true');
        tabs.panels[key].hidden = false;
      } else if(key !== 'panels') {
        el.classList.remove('active');
        el.setAttribute('aria-selected', 'false');
        tabs.panels[key].hidden = true;
      }
    });
  }

  tabs.admin.addEventListener('click', () => activateTab('admin'));
  tabs.student.addEventListener('click', () => activateTab('student'));

  // Load events with participants (Admin panel)
  async function loadEventsWithParticipants() {
    const eventsListDiv = document.getElementById('events-list');
    eventsListDiv.innerHTML = 'Loading...';

    try {
      const response = await fetch('get_events_with_participants.php');
      if(!response.ok) throw new Error('Failed to fetch events');
      const data = await response.json();

      if(data.length === 0){
        eventsListDiv.innerHTML = '<p>No events created yet.</p>';
        return;
      }

      let html = '';
      data.forEach(event => {
        html += `<section style="margin-bottom:1.5rem;">
          <h3>${event.title} <small style="color:#666;">(${event.date})</small></h3>
          <p>${event.description}</p>
          <strong>Participants (${event.participants.length}):</strong>`;

        if(event.participants.length === 0){
          html += '<p>No participants registered yet.</p>';
        } else {
          html += `<table>
            <thead><tr><th>Name</th><th>Email</th><th>Registration Date</th></tr></thead>
            <tbody>`;
          event.participants.forEach(p => {
            html += `<tr>
              <td>${p.name}</td>
              <td>${p.email}</td>
              <td>${new Date(p.registration_date).toLocaleString()}</td>
            </tr>`;
          });
          html += `</tbody></table>`;
        }
        html += `</section>`;
      });
      eventsListDiv.innerHTML = html;

    } catch(err){
      eventsListDiv.innerHTML = `<p class="message error">Error loading events: ${err.message}</p>`;
    }
  }

  // Populate event options (Student registration)
  async function populateEventOptions(){
    const eventSelect = document.getElementById('event-select');
    eventSelect.innerHTML = '<option>Loading events...</option>';

    try{
      const response = await fetch('get_event_options.php');
      if(!response.ok) throw new Error('Failed to fetch events');
      const events = await response.json();

      if(events.length === 0){
        eventSelect.innerHTML = '<option disabled>No events available</option>';
        return;
      }

      eventSelect.innerHTML = `<option disabled selected value="">Select an event</option>`;
      events.forEach(ev => {
        const option = document.createElement('option');
        option.value = ev.id;
        option.textContent = `${ev.title} (${ev.date})`;
        eventSelect.appendChild(option);
      });
    } catch(err){
      eventSelect.innerHTML = `<option disabled>Error loading events</option>`;
    }
  }

  // Event listeners for success/error messages
  function handleFormSubmission(form, messageDivId) {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const messageDiv = document.getElementById(messageDivId);
      messageDiv.style.display = 'none';

      const formData = new FormData(form);

      try {
        const response = await fetch(form.action, {
          method: form.method,
          body: formData
        });
        const text = await response.text();

        if(response.ok){
          messageDiv.textContent = text || 'Success';
          messageDiv.className = 'message success';
          form.reset();
          if(form.id === 'registration-form'){
            loadEventsWithParticipants();
          } else if(form.id === 'event-form'){
            loadEventsWithParticipants();
            populateEventOptions();
          }
        }else{
          messageDiv.textContent = text || 'Error';
          messageDiv.className = 'message error';
        }
      } catch(err){
        messageDiv.textContent = `Error: ${err.message}`;
        messageDiv.className = 'message error';
      }

      messageDiv.style.display = 'block';
      setTimeout(() => { messageDiv.style.display = 'none'; }, 6000);
    });
  }

  // On page load
  window.addEventListener('DOMContentLoaded', () => {
    loadEventsWithParticipants();
    populateEventOptions();

    handleFormSubmission(document.getElementById('registration-form'), 'registration-message');
    // Admin form success message handled inline due to no message div, so just reload events on submit
    const adminForm = document.getElementById('event-form');
    adminForm.addEventListener('submit', async e => {
      e.preventDefault();
      const form = e.target;
      const formData = new FormData(form);
      try {
        const response = await fetch(form.action, {method: form.method, body: formData});
        if (response.ok) {
          alert("Event created successfully.");
          form.reset();
          loadEventsWithParticipants();
          populateEventOptions();
        } else {
          const text = await response.text();
          alert(`Error creating event: ${text}`);
        }
      } catch (err) {
        alert(`Network error: ${err.message}`);
      }
    });
  });

})();
