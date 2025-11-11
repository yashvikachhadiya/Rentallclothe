<?php
//error_reporting(0);
$con= mysqli_connect("localhost","root","","rentalcloth");
if(isset($_POST['buy_now']))
{
       $email = $_POST['email'];  
       $address= $_POST['address'];  
       $mobile_no= $_POST['mobile'];  
       //$product=$_POST['category'];
      
   
      
     $sql="insert into orders values('$name','$address','$phone')";
     //$data=mysqli_query($con,$sql);
       //if($data)
         //        {
           //      echo "order successfully";
             //   }
        //else
          //   {
           //echo" Failed";
            // }
}
?>