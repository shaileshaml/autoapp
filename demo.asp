<%
' This is a Classic ASP file, typically using VBScript as the scripting language.

' Declare a variable
Dim strMessage

' Assign a value to the variable
strMessage = "Hello World from Classic ASP!"

' Write the contents of the variable to the web page
Response.Write(strMessage)

' Write a line break for better formatting
Response.Write("<br>")

' Display the current server time using the VBScript Time() function
Response.Write("The time on the server is: " & Time())
%>

<!DOCTYPE html>
<html>
<head>
    <title>Classic ASP Example for shailesh</title>
</head>
<body>
    <h1>Welcome to my Classic ASP Page!</h1>
    <p>This content is rendered by the server.</p>
</body>
</html>
