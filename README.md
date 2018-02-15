# What is this?

A test I took for Codeable. I failed it. They asked me to write a plugin that allowed for the shopping cart to inflate by 10% when certain products are in the cart. Products that trigger inflation should be configurable in the admin dashboard.

# Why did you fail it?

This is what our reviewer has to say:

- The plugin does not work and does not meet the request,
- Code quality not good. Direct plugin access not prevented, strings not translatable, mix of OOP and functional programming...
- Insufficient comments
- The project does not meet the exercise request because I can only apply the surcharge by attribute, but I can't really select which product to apply on a per-product basis (as we ask). 
- Additionally, the user should type the attribute name and this is not a good solution because a typo would break it. Much better a select box with attributes queried from existing ones.
