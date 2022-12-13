# SimplyCook Backend Task - Jon Robinson

I added comments around the code to explain my thinking and assumptions based on the brief. 
I will also add the more general, higher-level commentary here for reference. Generally code should speak for itself,
but being that this is an exercise with limited scope, there are pragmatic decisions that may not be the same for a production application.



## Assumptions & assertions

### Program scope
I've kept the actual running of the program light, with tests to see the logic in action, but I have not exposed an API etc. 
as that would result in more framework boilerplate than actual task-focussed code. The file/folder structure does represent how I might
have organised the code in a larger service.

I have also hardcoded a variety of config variables as constants in files for the same reason of scope.
In a production application, injecting these and having them stored in a database or similar would be preferable for testing and
allowing greater flexibility without a modification to application code.


### Expected dispatch date
I have assumed that when we talk about the 'The customer’s last successful billing event', that represents a completed transaction
and that the date we're calculating is the next delivery on their chosen cadence. This is as opposed to this representing a more transactional model:
i.e. the customer has paid today, and if that time is before 14:00, then the item is dispatched today, else tomorrow.

However, as the 14:00 cutoff is mentioned in the brief, I have inferred that it could be possible that this is the customer's first transaction,
and that in that case the first box would be dispatched ASAP. I have represented this with having no previous billing event in the code.

### Dispatch vs delivery date
I have assumed that a customer taking a break would, in practice, modify the dispatch date of their order which would in turn modify the delivery, rather than the delivery itself.
In the code, this equates to if we process and dispatch an order on Monday of week one, we would expect the delivery to occur on Wednesday of week one (given a two day lead time). 
However, if the customer has a one week break, we delay the dispatch by seven days (to Monday week two), and the delivery occurs on Wednesday of week two. 

### Delivery window
The attached screenshot shows a delivery window, and the final example seems to take into account the bank holidays around Christmas,
I have not done this, I've simply stuck to a single, earliest possible delivery date based on the calculation and lead time of two days.


