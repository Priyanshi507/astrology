  <!-- INPUT CARD -->
  <div class="card">
    <div class="sec-lbl">Location &amp; Time</div>
    <div class="input-panel">
 
      <!-- State -->
      <div class="ig">
        <label>State</label>
        <select id="stateSelect" onchange="onStateChange()">
          <option value="">— Select State —</option>
          <option value="AN">Andaman &amp; Nicobar</option>
          <option value="AP">Andhra Pradesh</option>
          <option value="AR">Arunachal Pradesh</option>
          <option value="AS">Assam</option>
          <option value="BR">Bihar</option>
          <option value="CH">Chandigarh</option>
          <option value="CG">Chhattisgarh</option>
          <option value="DD">Dadra &amp; Nagar Haveli / Daman &amp; Diu</option>
          <option value="DL">Delhi</option>
          <option value="GA">Goa</option>
          <option value="GJ">Gujarat</option>
          <option value="HR">Haryana</option>
          <option value="HP">Himachal Pradesh</option>
          <option value="JK">Jammu &amp; Kashmir</option>
          <option value="JH">Jharkhand</option>
          <option value="KA">Karnataka</option>
          <option value="KL">Kerala</option>
          <option value="LA">Ladakh</option>
          <option value="LD">Lakshadweep</option>
          <option value="MP">Madhya Pradesh</option>
          <option value="MH">Maharashtra</option>
          <option value="MN">Manipur</option>
          <option value="ML">Meghalaya</option>
          <option value="MZ">Mizoram</option>
          <option value="NL">Nagaland</option>
          <option value="OD">Odisha</option>
          <option value="PY">Puducherry</option>
          <option value="PB">Punjab</option>
          <option value="RJ">Rajasthan</option>
          <option value="SK">Sikkim</option>
          <option value="TN">Tamil Nadu</option>
          <option value="TG">Telangana</option>
          <option value="TR">Tripura</option>
          <option value="UP">Uttar Pradesh</option>
          <option value="UK">Uttarakhand</option>
          <option value="WB">West Bengal</option>
        </select>
      </div>
 
      <!-- City -->
      <div class="ig">
        <label>City</label>
        <select id="citySelect" disabled onchange="onCitySelect()">
          <option value="">— Select State first —</option>
        </select>
      </div>
 
      <!-- Date -->
      <div class="ig">
        <label>Date (DD/MM/YYYY)</label>
        <input type="text" id="dateDisplay" placeholder="12/05/2026" maxlength="10"
       oninput="syncDateFromDisplay()" style="font-family:'DM Sans',sans-serif;font-size:.92rem;color:var(--text);letter-spacing:0"/>
        <input type="hidden" id="dateInput"/>
      </div>
 
      <!-- Time -->
      <div class="ig">
        <label>Local Time</label>
        <input type="time" id="timeInput" step="60"/>
      </div>
 
      <!-- UTC Offset -->
      <div class="ig">
        <label>UTC Offset (hrs)</label>
        <input type="number" id="utcOffset" step="0.5" placeholder="+5.5"/>
      </div>
 
      <!-- Hidden lat/lon (required by JS) -->
      <input type="hidden" id="lat"/>
      <input type="hidden" id="lon"/>
      <input type="hidden" id="cityInput"/>
      <input type="hidden" id="cityStatus"/>
 
      <!-- Coordinates display row -->
      <div class="coords-display" id="coordsRow" style="display:none">
        <div class="coord-pill">
          <span>Lat</span>
          <code id="latDisplay">—</code>
        </div>
        <div class="coord-pill">
          <span>Lon</span>
          <code id="lonDisplay">—</code>
        </div>
        <div class="coord-pill">
          <span>TZ</span>
          <code id="tzDisplay">IST +5:30</code>
        </div>
        <div class="geo-status ok" id="geoStatusMsg"></div>
      </div>
 
      <!-- Calculate button -->
      <button class="btn-calc" id="calcBtn" onclick="doCalculate()">
        ✦ &nbsp;Calculate All Planet Positions
      </button>
    </div>
    <div class="err-pill" id="errPill"></div>
  </div>