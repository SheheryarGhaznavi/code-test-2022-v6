Refactor :

    1 - Booking Controller :
        - Custom Request classes could be used for data validation in request.
        - Not necessary variables are too many , those logic can be completed in fewer lines of code in controller function.
        - ternary condition can be used instead of most if else conditions
        - Some variable doesn't have default values
        - try catch should have to be implemented

    2 - Booking Repository :
        - code is very congested
        - Code is divided into function so other function can also call it instead of writing the same logic again.
        - try catch should have to be implemented
        - ternary condition can be used instead of most if else conditions
        - Some variable doesn't have default values