document.addEventListener("DOMContentLoaded", function () {
  if (typeof FFOTP_Settings === "undefined" || !FFOTP_Settings.forms) return;

  Object.entries(FFOTP_Settings.forms).forEach(([formId, phoneField]) => {
    const formSelector = `#forminator-module-${formId}`;
    const checkFormInterval = setInterval(() => {
      const form = document.querySelector(formSelector);
      if (!form) return;

      const phoneInput = form.querySelector(`input[name="${phoneField}"]`);
      const submitButton = form.querySelector(".forminator-button-submit");

      if (!phoneInput || !submitButton) return;

      clearInterval(checkFormInterval);

      // Inject country selector + OTP UI
      const countrySelect = document.createElement("select");
      countrySelect.id = `ffotp-country-${formId}`;
      countrySelect.innerHTML = `
                <option value="+91">India (+91)</option>
                <option value="+977">Nepal (+977)</option>
            `;
      countrySelect.style.marginBottom = "6px";

      phoneInput.parentNode.insertBefore(countrySelect, phoneInput);

      const otpContainer = document.createElement("div");
      otpContainer.innerHTML = `
                <div id="recaptcha-container-${formId}"></div>
                <button type="button" id="send-otp-${formId}" style="margin-top:6px;">Send OTP</button>
                <div id="otp-loading-${formId}" style="display:none;">Sending...</div>
                <div id="otp-section-${formId}" style="display:none; margin-top: 10px;">
                    <input type="text" id="otp_code-${formId}" placeholder="Enter OTP" />
                    <button type="button" id="confirm-otp-${formId}" disabled>Verify OTP</button>
                </div>
                <button type="button" id="reset-phone-${formId}" style="display:none;">Change Number</button>
                <p id="error-message-${formId}" style="color:red; margin-top:6px;"></p>
            `;
      phoneInput.parentNode.appendChild(otpContainer);

      // Grab UI elements
      const sendBtn = document.getElementById(`send-otp-${formId}`);
      const otpInput = document.getElementById(`otp_code-${formId}`);
      const confirmBtn = document.getElementById(`confirm-otp-${formId}`);
      const resetBtn = document.getElementById(`reset-phone-${formId}`);
      const otpSection = document.getElementById(`otp-section-${formId}`);
      const loading = document.getElementById(`otp-loading-${formId}`);
      const errorBox = document.getElementById(`error-message-${formId}`);
      const countryCode = document.getElementById(`ffotp-country-${formId}`);

      let otpVerified = false;
      let recaptchaVerifier;

      function showError(msg) {
        errorBox.textContent = msg;
      }

      function clearError() {
        errorBox.textContent = "";
      }

      function initRecaptcha() {
        if (!recaptchaVerifier) {
          recaptchaVerifier = new firebase.auth.RecaptchaVerifier(
            `recaptcha-container-${formId}`,
            {
              size: "invisible",
              callback: function () {
                console.log("Recaptcha solved");
              },
              "expired-callback": function () {
                showError("Recaptcha expired. Please try again.");
              },
            }
          );
          recaptchaVerifier.render();
        }
      }

      phoneInput.addEventListener("input", () => {
        clearError();
        phoneInput.value = phoneInput.value.replace(/\D/g, "").substring(0, 10);
        sendBtn.disabled = phoneInput.value.length < 6;
      });

      sendBtn.addEventListener("click", () => {
        clearError();
        const fullPhone = countryCode.value + phoneInput.value;
        if (phoneInput.value.length < 6) {
          showError("Enter a valid phone number.");
          return;
        }

        initRecaptcha();
        loading.style.display = "block";
        sendBtn.style.display = "none";

        firebase
          .auth()
          .signInWithPhoneNumber(fullPhone, recaptchaVerifier)
          .then((result) => {
            window[`confirmationResult_${formId}`] = result;
            loading.style.display = "none";
            otpSection.style.display = "block";
            resetBtn.style.display = "inline-block";
          })
          .catch((error) => {
            console.error(error);
            showError("OTP sending failed. Try again.");
            loading.style.display = "none";
            sendBtn.style.display = "inline-block";
          });
      });

      otpInput.addEventListener("input", () => {
        otpInput.value = otpInput.value.replace(/\D/g, "").substring(0, 6);
        confirmBtn.disabled = otpInput.value.length !== 6;
      });

      confirmBtn.addEventListener("click", () => {
        const code = otpInput.value;
        if (code.length !== 6) {
          showError("Enter the 6-digit OTP.");
          return;
        }

        window[`confirmationResult_${formId}`]
          .confirm(code)
          .then(() => {
            otpVerified = true;
            confirmBtn.innerHTML = "✔ Verified";
            confirmBtn.disabled = true;
            phoneInput.removeAttribute("required");
            submitButton.disabled = false;
          })
          .catch(() => {
            showError("Incorrect OTP. Try again.");
          });
      });

      resetBtn.addEventListener("click", () => {
        phoneInput.value = "";
        otpInput.value = "";
        otpVerified = false;
        otpSection.style.display = "none";
        sendBtn.style.display = "inline-block";
        sendBtn.disabled = true;
        confirmBtn.disabled = true;
        resetBtn.style.display = "none";
        phoneInput.setAttribute("required", "true");
        submitButton.disabled = true;
      });

      form.addEventListener("submit", function (e) {
        if (!otpVerified) {
          e.preventDefault();
          showError("Please verify OTP before submitting.");
        }
      });

      submitButton.disabled = true;
    }, 300);
  });
});
