## Documenation

### Display the login/logout links

```
show_uli_app_login()
```


### Display the signup form

```
show_uli_app_signup($preference_key, $preference_label, $form_title)
```

If the parameters are not present, the form will be set with the ones entered in the settings page


### Get all the preferences, returns an array with the keys and labels

```all_uli_app_preferences()
```



### Check if the user is logged in

```$uli_app->is_logged()
```



### Check if the user is a member

```$uli_app->is_member()
```


### Get User Info

```$uli_app->user()
```