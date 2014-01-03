#
# Extending model funcionality
# ----------------------------
#
# <?php
# User::observe(new UserObserver);
#

class UserObserver
  creating: ->
    # before create

  created: ->
    # after create

    # Create the Mailer using your created Transport
    transport = Swift_MailTransport.newInstance()
    mailer = Swift_Mailer.newInstance(transport)

    # Create a message
    message = Swift_Message.newInstance('Subject').
      setFrom({'endel.dreyer@gmail.com':'Endel'}).
      setTo({'edreyer@doubleleft.com':'Endel Dreyer'}).
      setBody('Testing message')

    # Send the message
    result = mailer.send(message)

  updating: ->
    # before update

  updated: ->
    # after update

  saving: ->
    # before save

  saved: ->
    # after save

  deleting: ->
    # before delete

  deleted: ->
    # after delete

  restoring: ->
    # before restore

  restored: ->
    # after restore
