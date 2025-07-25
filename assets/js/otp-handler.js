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

      const otpContainer = document.createElement("div");
      otpContainer.innerHTML = `
        <div id="recaptcha-container-${formId}"></div>
        <button type="button" id="send-otp-${formId}" class="ffotp-send-otp">Send OTP</button>
        <div id="otp-loading-${formId}" style="display:none;">Sending...</div>
        <div id="otp-section-${formId}" class="ffotp-otp-ui" style="display:none; margin-top: 10px;">
            <div class="otp-block">
                <input type="text" id="otp_code-${formId}" class="ffotp-otp_code" placeholder="Enter OTP" />
                <button type="button" id="confirm-otp-${formId}" class="ffotp-confirm-otp" disabled>Verify OTP</button>
                <button type="button" id="reset-phone-${formId}" class="ffotp-reset-phone" style="display:none;">Change Number</button>
            </div>
            
        </div>
        <p id="error-message-${formId}" style="color:red; margin-top:6px;"></p>
        `;

      phoneInput.parentNode.appendChild(otpContainer);

      // Grab UI elements
      const sendBtn = form.querySelector(`#send-otp-${formId}`);
      const otpInput = form.querySelector(`#otp_code-${formId}`);
      const confirmBtn = form.querySelector(`#confirm-otp-${formId}`);
      const resetBtn = form.querySelector(`#reset-phone-${formId}`);
      const otpSection = form.querySelector(`#otp-section-${formId}`);
      const loading = form.querySelector(`#otp-loading-${formId}`);
      const errorBox = form.querySelector(`#error-message-${formId}`);

      let otpVerified = false;
      window[`recaptchaVerifier_${formId}`] = null;

      function showError(msg) {
        errorBox.textContent = msg;
      }

      function clearError() {
        errorBox.textContent = "";
      }

      function initRecaptcha(formId) {
        const recaptchaId = `recaptcha-container-${formId}`;
        const container = document.getElementById(recaptchaId);

        if (!container) {
          console.warn(`Recaptcha container #${recaptchaId} not found.`);
          return;
        }

        // Prevent re-initialization
        if (!window[`recaptchaVerifier_${formId}`]) {
          try {
            window[`recaptchaVerifier_${formId}`] =
              new firebase.auth.RecaptchaVerifier(recaptchaId, {
                size: "invisible",
                callback: function () {
                  console.log("Recaptcha solved");
                },
                "expired-callback": function () {
                  const errorBox = document.getElementById(
                    `error-message-${formId}`
                  );
                  if (errorBox)
                    errorBox.textContent =
                      "Recaptcha expired. Please try again.";
                },
              });

            window[`recaptchaVerifier_${formId}`].render().catch((err) => {
              console.error("Recaptcha render failed:", err);
            });
          } catch (e) {
            console.error("Recaptcha already initialized or invalid:", e);
          }
        }
      }

      phoneInput.addEventListener("input", () => {
        clearError();
        phoneInput.value = phoneInput.value.replace(/\D/g, "").substring(0, 10);
        sendBtn.disabled = phoneInput.value.length < 6;
      });

      sendBtn.addEventListener("click", () => {
        clearError();
        const itiWrapper = phoneInput.closest(".iti");
        const dialCodeEl = itiWrapper?.querySelector(
          ".iti__selected-dial-code"
        );
        const dialCode = dialCodeEl?.textContent.trim() || "";
        const localNumber = phoneInput.value.replace(/\D/g, "");
        const fullPhone = dialCode + localNumber;

        if (phoneInput.value.length < 6) {
          showError("Enter a valid phone number.");
          return;
        }
        if (!/^\+\d{6,15}$/.test(fullPhone)) {
          showError(
            "Please enter a valid international phone number (e.g., +919876543210)."
          );
          return;
        }

        initRecaptcha(formId);
        loading.style.display = "block";
        sendBtn.style.display = "none";

        firebase
          .auth()
          .signInWithPhoneNumber(
            fullPhone,
            window[`recaptchaVerifier_${formId}`]
          )

          .then((result) => {
            window[`confirmationResult_${formId}`] = result;
            loading.style.display = "none";
            otpSection.style.display = "block";
            resetBtn.style.display = "inline-block";
            phoneInput.setAttribute("readonly", "true");
phoneInput.classList.add("ffotp-locked");

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
        phoneInput.removeAttribute("readonly");
phoneInput.classList.remove("ffotp-locked");

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
