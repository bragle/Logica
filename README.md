# Logica
A tiny programming language for making custom logic flow in PHP applications

*note: spaces are there for a reason. this language is really simple and requires spaces to split calls*

Howto:

Functions are defined within \[square brackets\].

Available functions:

**if**:
```
a standard if block
param 1 must be a boolean (or something that returns a boolean like an operation)
must be ended with [fi]
```

**jump**:
```
jumps to a line specified by param 1
note: lines start at 0
```

**cat**:
```
will concatenate all parameters
```

**print**:
```
will concatenate all parameters and print the result
```

**exit**:
```
will exit
```

Available operators with two params:
```
==
===
!=
!==
>
>=
<
<=
%
+
-
||
&&
```

Available operators with one param:
```
!!
!
```

Operations are defined with (brackets) and either one param or two params.

Variables are always global, and can be overwritten. Variables should be set like this:

*variable name* = *value*

Example:

```
counter = 0

[if (counter > (10 - 3))]

	[print [cat counter " is higher than " 7]]

[fi]

[if (counter < 10)]

	counter = (counter + 1)

	[jump 3]

[fi]

[if (counter)]

	[print "counter is higher than " [cat "1" 0]]

[fi]

[exit]

[print "this message will never be printed"]
```

Example output:

```
8 is higher than 7
9 is higher than 7
10 is higher than 7
counter is higher than  10
```
