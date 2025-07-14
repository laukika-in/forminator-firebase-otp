document.addEventListener("DOMContentLoaded", function () {
  if (typeof FFOTP_Settings === "undefined" || !FFOTP_Settings.forms) return;

  Object.entries(FFOTP_Settings.forms).forEach(([formId, phoneField]) => {
    const allForms = document.querySelectorAll(`#forminator-module-${formId}`);

    allForms.forEach((form, index) => {
      const uniqueId = `${formId}_${index}`;
      const phoneInput = form.querySelector(`input[name="${phoneField}"]`);
      const submitButton = form.querySelector(".forminator-button-submit");

      if (!phoneInput || !submitButton) return;

      const otpContainer = document.createElement("div");
      otpContainer.innerHTML = `
        <div class="recaptcha-container" id="recaptcha-container-${uniqueId}"></div>
        <button type="button" class="ffotp-send-otp" id="send-otp-${uniqueId}">Send OTP</button>
        <div id="otp-loading-${uniqueId}" style="display:none;">Sending...</div>
        <div id="otp-section-${uniqueId}" class="ffotp-otp-ui" style="display:none;">
          <div class="otp-block">
            <input type="text" id="otp_code-${uniqueId}" placeholder="Enter OTP" />
            <button type="button" id="confirm-otp-${uniqueId}" disabled>Verify OTP</button>
            <button type="button" id="reset-phone-${uniqueId}" style="display:none;">Change Number</button>
          </div>
        </div>
        <p id="error-message-${uniqueId}" style="color:red; margin-top:6px;"></p>
      `;
      phoneInput.parentNode.appendChild(otpContainer);

      // Scoped selectors inside current form
      const sendBtn = form.querySelector(`#send-otp-${uniqueId}`);
      const otpInput = form.querySelector(`#otp_code-${uniqueId}`);
      const confirmBtn = form.querySelector(`#confirm-otp-${uniqueId}`);
      const resetBtn = form.querySelector(`#reset-phone-${uniqueId}`);
      const otpSection = form.querySelector(`#otp-section-${uniqueId}`);
      const loading = form.querySelector(`#otp-loading-${uniqueId}`);
      const errorBox = form.querySelector(`#error-message-${uniqueId}`);
      const recaptchaId = `recaptcha-container-${uniqueId}`;

      let otpVerified = false;

      function showError(msg) {
        errorBox.textContent = msg;
      }

      function clearError() {
        errorBox.textContent = "";
      }

      function initRecaptcha() {
        const container = form.querySelector(`#${recaptchaId}`);
        if (!container || container.hasChildNodes()) return;

        if (!window[`recaptchaVerifier_${uniqueId}`]) {
          try {
            window[`recaptchaVerifier_${uniqueId}`] =
              new firebase.auth.RecaptchaVerifier(container, {
                size: "invisible",
                callback: function () {
                  console.log("Recaptcha solved");
                },
                "expired-callback": function () {
                  showError("Recaptcha expired. Please try again.");
                },
              });

            window[`recaptchaVerifier_${uniqueId}`].render().catch((err) => {
              console.error("Recaptcha render failed:", err);
            });
          } catch (e) {
            console.warn("Recaptcha already initialized or invalid:", e);
          }
        }
      }

      phoneInput.addEventListener("input", () => {
        clearError();
        phoneInput.value = phoneInput.value
          .replace(/\\D/g, "")
          .substring(0, 15);
        sendBtn.disabled = phoneInput.value.length < 6;
      });

      sendBtn.addEventListener("click", () => {
        clearError();
        const fullPhone = phoneInput.value.trim();

        if (fullPhone.length < 6) {
          showError("Enter a valid phone number.");
          return;
        }

        initRecaptcha();
        loading.style.display = "block";
        sendBtn.style.display = "none";

        firebase
          .auth()
          .signInWithPhoneNumber(
            fullPhone,
            window[`recaptchaVerifier_${uniqueId}`]
          )
          .then((result) => {
            window[`confirmationResult_${uniqueId}`] = result;
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
        otpInput.value = otpInput.value.replace(/\\D/g, "").substring(0, 6);
        confirmBtn.disabled = otpInput.value.length !== 6;
      });

      confirmBtn.addEventListener("click", () => {
        const code = otpInput.value;
        if (code.length !== 6) {
          showError("Enter the 6-digit OTP.");
          return;
        }

        window[`confirmationResult_${uniqueId}`]
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
    });
  });
});
