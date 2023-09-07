
    <script type="text/javascript">
      function evaluate_employee( value ){
      //envia datos por ajax
        $.ajax({
          type : 'post',
          url : 'ajax/evaluateEmploye.php',
          cache : false,
          data : { score : value },
          success : function ( dat ){
            alert(dat);
            location.href = "index.php?scr=home";
          }
        });
      }
    </script>