INSTALL
-------

Step 1:

Configure fail2ban correctly.

Visit this article for fail2ban installation:
- https://www.linode.com/docs/security/using-fail2ban-to-secure-your-server-a-tutorial/

Step 2:

Create new filter for fail2ban.

Add `filter.d/playsms.conf` to `/etc/fail2ban/filter.d/`

Step 3:

Enable the filter to get fail2ban watch over playSMS log files.

Add `jail.d/playsms.local` to `/etc/fail2ban/jail.d/`

Step 4:

Reload fail2ban.

```
fail2ban-client reload
```

Monitor:

```
fail2ban-client status
fail2ban-client status sshd
fail2ban-client status playsms
```

Monitor fail2ban log file:

```
tail -f /var/log/fail2ban.log
```
