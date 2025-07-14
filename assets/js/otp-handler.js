document.addEventListener("DOMContentLoaded", function () {
  if (typeof FFOTP_Settings === "undefined" || !FFOTP_Settings.forms) return;

  Object.entries(FFOTP_Settings.forms).forEach(([formId, phoneField]) => {
    const formSelector = `#forminator-module`;
    const checkFormInterval = setInterval(() => {
      const form = document.querySelector(formSelector);
      if (!form) return;

      const phoneInput = form.querySelector(`input[name="${phoneField}"]`);
      const submitButton = form.querySelector(".forminator-button-submit");

      if (!phoneInput || !submitButton) return;

      clearInterval(checkFormInterval);

      const otpContainer = document.createElement("div");
      otpContainer.innerHTML = `
                <div id="recaptcha-container"></div>
                <button type="button" id="send-otp" style="margin-top:6px;">Send OTP</button>
                <div id="otp-loading" style="display:none;">Sending...</div>
                <div id="otp-section" style="display:none; margin-top: 10px;">
                    <input type="text" id="otp_code" placeholder="Enter OTP" />
                    <button type="button" id="confirm-otp" disabled>Verify OTP</button>
                <button type="button" id="reset-phone" style="display:none;">Change Number</button>
                </div>
                <p id="error-message" style="color:red; margin-top:6px;"></p>
            `;
      phoneInput.parentNode.appendChild(otpContainer);

      // Grab UI elements
      const sendBtn = document.getElementById(`send-otp`);
      const otpInput = document.getElementById(`otp_code`);
      const confirmBtn = document.getElementById(`confirm-otp`);
      const resetBtn = document.getElementById(`reset-phone`);
      const otpSection = document.getElementById(`otp-section`);
      const loading = document.getElementById(`otp-loading`);
      const errorBox = document.getElementById(`error-message`);
      const countryCode = document.getElementById(`ffotp-country`);

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
            `recaptcha-container`,
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
        const fullPhone = phoneInput.value.replace(/\s/g, "");

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
