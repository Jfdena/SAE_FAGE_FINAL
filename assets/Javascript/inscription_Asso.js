  function handleRegistration(event) {
        event.preventDefault(); 

        const formWrapper = document.getElementById('formWrapper');
        const successMessage = document.getElementById('successMessage');
        const submitBtn = document.getElementById('submitBtn');
        const btnLabel = document.getElementById('btnLabel');
        const btnLoader = document.getElementById('btnLoader');

        submitBtn.disabled = true;
        btnLabel.textContent = "Traitement en cours...";
        btnLoader.style.display = "inline-block";

        setTimeout(() => {
          formWrapper.style.display = 'none';
          successMessage.style.display = 'block';
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 1500); 
      }