import sys
print sys.path
from weboob.modules.societegenerale import *

login = sys.argv[1]
password = sys.argv[2]

if len(sys.argv) > 3:
	accountId = sys.argv[3]
else:
	accountId = None

browser = SocieteGenerale(login, password)
browser.login()

if not accountId:
	accounts = browser.get_accounts_list()

	for account in accounts:
		d = (account.id, account.label, str(account.balance))
		l = ';'.join(d)
		print l.encode('utf-8')
else:
	account = browser.get_account(accountId)
	d = ('A', account.id, account.label, str(account.balance))
	l = ';'.join(d)
	print l.encode('utf-8')

	transactions = browser.get_history(account)

	for transaction in transactions:
		d = ('T', transaction.date, transaction.label, str(transaction.amount))
		l = ';'.join(d)
		print l.encode('utf-8')