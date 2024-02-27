<div id="login" >
    <img src="<?php echo IMAGES."logo.png"; ?>" />
    <h3 class="title-form"> Logon de funcionários </h3>
    <form method="post" action="?op=login"> 
        <label> Login: 
            <input type="text" name="login" required class="input text-input"/>
        </label>
        <p class="v-separator"> &nbsp;</p>
        <label>
            Senha: 
            <input type="password" name="senha" required  class="input text-input"/>
        </label>
        <p class="v-separator"> &nbsp; </p>
        <p>
            <input type="submit" name="submeter" value="login" class="btn submit green3-grad-back">
        </p>    
    </form>
</div>
<style>
    #login img{
        width: 350px;
        margin-bottom: 20px;
        box-shadow: 0 0 10px #555;
        border-radius: 3px;
    }
    #login {
        background: white;
        box-shadow: 0 4px 2px rgb(44, 102, 175);
        width: 350px;
        padding: 20px;
        position: relative;
        left: 50%; 
        margin-left: -195px;
        text-align: right;
        margin-top: 10px;
        padding-top: 70px;
        border-radius: 10px 10px 0 0;
    }
    #login .input{
        width: 200px;
    }
    .title-form{
        text-align: left;
    }
    #body{
        background-image: linear-gradient(bottom, rgb(255,255,255) 82%, rgb(145,143,145) 0%);
        background-image: -o-linear-gradient(bottom, rgb(255,255,255) 82%, rgb(145,143,145) 0%);
        background-image: -moz-linear-gradient(bottom, rgb(255,255,255) 82%, rgb(145,143,145) 0%);
        background-image: -webkit-linear-gradient(bottom, rgb(255,255,255) 82%, rgb(145,143,145) 0%);
        background-image: -ms-linear-gradient(bottom, rgb(255,255,255) 82%, rgb(145,143,145) 0%);

        background-image: -webkit-gradient(
                linear,
                left bottom,
                left top,
                color-stop(0.82, rgb(255,255,255)),
                color-stop(0, rgb(145,143,145))
        );
    }
</style>
