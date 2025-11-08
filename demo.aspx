<%@ Page Language="C#" %>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <title>Inline Code Example</title>
</head>
<body>
  Hello Shailesh Welcome to aspx pages 
    <form id="form1" runat="server">
        <div>
            <asp:Label ID="lblMessage" runat="server" Text="Initial Message"></asp:Label>
            <br />
            <asp:Button ID="btnClickMe" runat="server" Text="Click Me" OnClick="btnClickMe_Click" />
        </div>
    </form>

    <script runat="server">
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                lblMessage.Text = "Page loaded at: " + DateTime.Now.ToLongTimeString();
            }
        }

        protected void btnClickMe_Click(object sender, EventArgs e)
        {
            lblMessage.Text = "Button clicked at: " + DateTime.Now.ToLongTimeString();
        }
    </script>
</body>
</html>
