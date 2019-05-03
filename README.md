# Logica
A tiny programming language for making custom logic flow in PHP applications

Howto:

Functions are defined within [square brackets].

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
++
--
```

Example:

```

counter = 0

[if (counter > (10 - 3))]

    [print [cat counter " is higher than " 7]]

[fi]

[if (counter < 10)]

    counter = (++ counter)

    [jump 3]

[fi]

[print "counter is higher than " [cat "1" 0]]

[exit]

[print "this message will never be printed"]

```
Operations are defined with (brackets) and either one param or two params.
