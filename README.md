Lozeil
======

[Lozeil](http://lozeil.org) is a **PHP-based cashflow management web application.** Its goal is to improve and simplify the way that you manage your cash.
This involves bank's statement importation from standard format, real-time monitoring, statistics, simulations...<br />
Lozeil has an embedded machine learning system which will keep a record of the choices that you make and _cleverly/automatically_ make them thereafter.

## Installation

### Requirement
* Server supporting PHP
* Mysql database

### Build your own Lozeil

First, clone a copy of Lozeil's git repo by running:
```bash
git clone git://github.com/noparking/lozeil.git
```

Setup your installation by running bot.php located in cli/, give the database's information and create a default user:
```bash
php bot.php --setup
```

Your done! Try running index.php to test your installation.

## Usage

Those steps need to be done in order, as they aren't independent.<br />
The navigation is done by using the top menu, accessible by clicking on 'More'.

### Import your bank statement
* Supported format: OFX, QIF

The only step before using Lozeil is to export your bank statement in a supported format directly from your bank's website.<br />
Tell Lozeil which bank you want to handle by using the top menu, manage the banks. Add it using the form and don't forget to tick the checkbox corresponding, otherwise it won't be took into consideration.<br />
The next step is to import your statement into Lozeil. To do so, go to the top menu and choose your file by clicking on 'Import bank statement'. Select your bank and 'Ok'.<br />
You are now redirected onto your records, called 'writings' in Lozeil's Language. You can appreciate the timeline that gives you a good overview of what's going on.

If you are using other source of payment, you'll also be able to import it using 'import writings from source'.
* Supported source: Paybox .csv

### Categorize & refine
Go to 'manage the categories' through the top menu. In this section you can define as much categories as you need with a default vat rate (e.g., telecommunication - 19.6, bank charges - 0.0, wages - 0.0, activity A - 19.6, activity B - 5.5... aso)<br />
You also need to create a 'vat category', which will be use to automatically calculate the vat to charge.<br />
When you're done, go back to the balance sheet and manually categorize as much writings as you can by using the checkboxes and the select menu at the bottom of the table or by editing one at a time using the pen icon.
This is an essential step as it's how Lozeil is going to learn from you.<br />

Occasionally, your bank isn't as accurate as you wanted to. Some writings are actually the merging of several writings (e.g. cheque remittance). To deal with those situation, use the split icon that shows up when hovering a line.

### Build your forecast bugdet
From your actual budget you can build your forecast. To do so use the plus icon that shows up when hovering the writings. Tell lozeil the frequency of this writing and it will create the corresponding one's, which won't be affiliated to a bank.

### Statistics
The statistics are accessible through 'consult statistics', you can have a precise overview of your cashflow filtered by categories and banks scalable by day, week and month.

### Simulations
See how a new income/outcome would impact your accounting and your forecast bugdet.

### See the machine learning effect
To assess Lozeil's intelligence, you need to import your bank statement at a post date of your last import, including new transactions. You'll notice that the categorization is done all by itself.
If some lines aren't, that's because the writing is ambiguous or unknown, keep doing the manual categorization to train Lozeil.

### Bank reconciliation
The last step of Lozeil's process is the bank reconciliation. To do so, you have to merge the exact same writings from the bank and from your forecast. The filters will help you find them.
Once you have found one, simply drag and drop the lines over the other to do the merge.<br />

## Running the unit tests

To run the unit tests you need to update the submodule simpletest:
```bash
git submodule init
```
```bash
git submodule update
```

Once done, you're all set up! The tests are located in tests/unit/.

## Links

* Repository: git://github.com/noparking/lozeil.git
* Simpletest: <https://github.com/simpletest/simpletest>
* Lozeil.org: <http://lozeil.org>
* Issues: > to-do link to bug tracker..
