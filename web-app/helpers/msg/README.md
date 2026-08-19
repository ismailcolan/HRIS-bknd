# The [msg](msg.php) helper #

``msg::get|set|put(); ``

Serves the user messages (optional session use). Can use [Bootstrap alerts dismissable](http://getbootstrap.com/components/#alerts-dismissible) styles.

### Session ###
The [msg.php](msg.php) class ***set*** & ***get*** methods trigger session use, while the ***put*** method just prints a message to the output.  

### The class methods ###
* **Set** stores a message in the session variable. The ***get*** method shows it on the next page load.
	
	`msg::set("Site message come here!", "success")` 
	
	Method is created to serve save/delete actions, where the action completion will trigger page reload.
    
* **Get** shows any stored in the session variable messages.
	
	`msg::get()`

* **Put** shows a single message, without a session connection.

	`msg::put("Site message come here!", "plain")`

### Message types ###
* Regular messages:

	![Regular messages](../repo-files/msg-types-regular.jpg "Regular messages examples")
 
* Bootstrap messages: 

	To activate [Bootstrap alerts dismissable](http://getbootstrap.com/components/#alerts-dismissible) styles, just change the constant `const USE_BOOTSTRAP = false;` value to `true`.

	![Bootstrap messages](../repo-files/msg-types-bootstrap.jpg "Bootstrap messages examples")