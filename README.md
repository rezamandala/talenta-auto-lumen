
# Automate Talenta ClockIn - Clock Out

## Documentation
This documentation will provide a tutorial for using manual and automatic clock-in and clock-out "talenta" applications via web https://hr.talenta.co

### Preperation

- Fork this repo
	- ![fork repo](./images/ss/1.png)
	- ![fork repo](./images/ss/2.png)
- create a repository secret in the settings - secrets and variables - action menu in your repository (example: **https://github.com/{your-github-username}/automation-attendance**)
	- ![creating repository secret](./images/ss/3.png)
	- ![creating repository secret 2 ](./images/ss/6.png)
- then, create a secret like this
	- ![creating repository secret 2](./images/ss/17.png)
- get your langitude and longitude from gmaps
	- ![pointing lat long from gmpas](./images/ss/5.png)
- get your user_id by inspecting the element and get MOE_DATA from local storage
	- ![pointing lat long from gmpas](./images/ss/18.png)
- find USER_ATTRIBUTE_UNIQUE_ID from the value of MOE_DATA then get the first string before "_" symbol, ex: XXXXX_YYY (XXXXX is your user_id and YYYY is organization id )
- go to action tab and accept the tnc
	- ![accept tnc action](./images/ss/7.png)
- Go to check workflow to check whether all the settings in the repository secret are correct
	- ![check action](./images/ss/8.png)
	- if your check is failed, then you must insert repository secret correctly
	- ![success](./images/ss/10.png)
	- next setting for clockin - clockout if your check is success
	- ![success](./images/ss/9.png)
- Enable Clock-in and Clock-out action
	- ![success](./images/ss/11.png)
- The clock-in schedule is set at around 7.30-08.30 and the clock-out is set at around 17.00 - 18.00. Sometimes the clock-in shows more time than specified due to the system queue, if possible I recommend manual clock-in
	- you can run workflow manually or You can wait for the schedule to run
	- ![success](./images/ss/12.png)

### Additional
- You can change the schedule if your time scheme is different. setting schedules using crontab, make sure you understand the rules in crontab
	- ![edit scheduler](./images/ss/13.png)
    - ![edit scheduler](./images/ss/14.png)
    - ![edit scheduler](./images/ss/15.png)
