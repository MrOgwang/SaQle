(() => (){
     document.getElementById('signinForm').addEventListener('submit', function(e) {
         e.preventDefault();

         const formData = {
             email: this.email.value,
             password: this.password.value
         };

         console.log(formData);

         // Replace with your login request
         alert('Sign in submitted');
     });
});
