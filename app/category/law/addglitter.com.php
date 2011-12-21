<?php $catalogLink = '<div style="padding: 2px; width: 380px; color: #000; background-color: #eee; border: 1px solid #ccc; font-size: 12px;">
      <div style="float: left;">Add Glitter to any image!</div>
      <div style="float: right; font-size: 10px;">Provided by <a href="http://www.addglitter.com">AddGlitter.com</a></div>
      <form action="http://www.addglitter.com/mark-image.asp" style="clear: both; margin: 0px; padding: 0px;" method="get">
      <table cellspacing="0" cellpadding="0">
        <tr>
          <td>
            Enter an image URL:
          </td>
          <td>
              <input type="text" value="http://" style="width: 200px;" id="imageurl" name="imageurl">
          </td>
          <td>
            <input type="submit" value="Go">
          </td>
        </tr>
      </table>
    </form>
  </div>'; include '../view.php';