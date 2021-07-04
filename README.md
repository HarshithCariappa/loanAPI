<p align="center"><h1>Simple Loan API</h1></p>

## About Loan API

It is a simple API built with Laravel 8 framework for demo purpose. It contains features such as:

- Authentication using Sanctum with the bearer token.
- Login and Registration api.
- API to apply for loan and repay the loan. 
- Used sqlite to make the setup easy.
- Unit testing is done using phpUnit with code coverage.

## Install

   - Clone / download the project.
   - Create a "database.sqlite" file within the "loanAPI\database" folder. 
   - Create a "testdatabase.sqlite" file withing the "loanAPI\tests" folder.
   - Open terminal and navigate to the "loansAPI" folder (project home) and Run the below commands in the terminal.
        
            composer install
            php artisan migrate
            php artisan db:seed
            php artisan serve
   - Now the application is running.
   - The details of how to authenticate and call the api's are give below in *Project Details* section.

## Project Details 

**Using Postman**  
In Postman add these under header section to accept json value, (Do this for all API calls)

    Key : Accept
    value : application/json


- **Registration**
  - End Point : `/api/register`
  - Request Type : `POST`
  - Body Section (Pass the below parameters in the body section of Postman) : 
        
        firstName             | Required | String
        lastName              | Required | String
        password              | Required | String 
        password_confirmation | Required | String
        phoneNumber           | Required | Numeric | Should be 10 digits  | Should be unique
  
  - Responses : 
  
        Status code  | Responce                    | Reason
            422      | The given data was invalid  | Data validation failed, check the responce message 
            200      | Successfull                 | Registration was successfull

- **Login**
  - End Point : `/api/login`
  - Request Type : `POST`
  - Body Section (Pass the below parameters in the body section of Postman) :  
        
        phoneNumber | Required 
        password    | Required 
        
  - Responses : 
  
        Status code  | Responce                    | Reason
            401      | Bad Credentials             | Login failed, incorrect userName or password
            200      | Successful                     | Login was successfull
            422      | The given data was invalid  | Missing a required parameter
            
  - Successful json response : 
        
        {
            "user": {
                "uid": 1,
                "first_name": "Harshith",
                "last_name": "Cariappa",
                "phone_number": "9945564586",
                "created_at": "2021-07-04T09:59:20.000000Z",
                "updated_at": "2021-07-04T09:59:20.000000Z"
            },
            "token": "8|wVzaqd72BX6cZ8SA7OhOGuuLqlzCCLyPbIYAcTle"
        }
        
- **Apply for a loan**                   
    - End Point : `/api/applyLoan`
    - Request Type : `POST`
    - Body Section (Pass the below parameters in the body section of Postman) :  
        
            loanAmount    | Required  | numeric
            loanTerm      | Required  | numeric 
            monthlyIncome | Required  | numeric
            loanType      | Required  | numeric (all avalilable loan types are given below, this field take the 'loan_type_id' from below table)
        
    - **Available loan types** 
            
            loan_type_id   |  loan_type          |  interest_rate 
                1          |  Home Loan          |      0.066
                2          |  Car Loan           |      0.08
                3          |  Agriculture Loan   |      0.045

    - Responses : 
    
            Status code  | Responce                    | Reason
                403      | Rejected                    | Already have a uncleared loan
                406      | Rejected                    | On converting Monthly income to weekly income the weekly income insufficient to do weekly repayments
                401      | Unauthenticated             | Authentication failed, incorrect token passed
                200      | Approved                    | Loan request is approved
                422      | The given data was invalid  | Missing a required parameter
                
   - Approved Response json : 
            
            {
                "status": "Approved",
                "message": "Your loan request is approved",
                "loanId": 4,
                "totalRepayAmount": 1066,
                "weeklyRepaymentAmount": 106.6
            }

- **Repay a loan**                   
    - End Point : `/api/RepayLoan`
    - Request Type : `POST`
    - Body Section (Pass the below parameters in the body section of Postman) :  
        
            repayAmount    | Required  | numeric
   
   - **Note : repayAmount should be same as the weeklyRepaymentAmount given by applyLoan API's Approved response**

    - Responses : 
    
            Status code  | Responce                    | Reason
                403      | Failed                      | No existing loan
                406      | Failed                      | Incorrect weekly repayment amount (Should be same as weeklyRepaymentAmount provided in response)
                401      | Unauthenticated             | Authentication failed, incorrect token passed
                200      | Successful                  | Weekly repayment was successful
                422      | The given data was invalid  | Missing a required parameter
                
   - Approved Response json : 
            
            {
                "status": "Successful",
                "message": "Loan repayment successful",
                "loanRepayId": 32,
                "balanceAmount": 852.8
            }
            
- **Running the unit test**
    - Open the terminal and navigate to the home folder of the project (loansAPI) and run the below command.
    
            .\vendor\bin\phpunit.bat
            
    - To check Code coverage report open the **loansAPI\reports\index.html** file in the browser.
             

## To Be Done

- Logging has to be added.
- Exception handling has to be done.
- Script to install the app in one go has to be done.
- Improving the documentation and adding all the responses.
- Few more test cases for the unit testing can be added.


## Completed Tasks

- Completed all the functionalities of the API
- Created the Authentication using Sanctum
- Written 7 test cases with 50 assertions.
- Did the unit testing for all the api's with overall **92.41%** of code coverage and **100%** code coverage of all manually written code.
