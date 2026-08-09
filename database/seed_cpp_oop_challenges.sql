-- ============================================================================
-- Seed: 10 C++ OOP Coding Challenges
-- Covers: classes & objects, constructors, encapsulation, inheritance,
--         method overriding, function overloading, runtime polymorphism,
--         abstract classes (abstraction), operator overloading, static members.
--
-- Safe to re-run? NO — run ONCE. It inserts 10 challenges + 30 test cases.
--
-- IMPORTANT: set the target course.
--   By default it picks the first published course whose title contains "C++".
--   If your course has a different title, replace the lookup below, e.g.:
--     SET @cid := 12;
--   To see available courses first:  SELECT id, title, status FROM courses;
-- ============================================================================

SET @cid := (SELECT id FROM courses WHERE status='published' AND title LIKE '%C++%' ORDER BY id LIMIT 1);

-- ----------------------------------------------------------------------------
-- 1. Bank Account Class  (Classes & Objects)
-- ----------------------------------------------------------------------------
INSERT INTO coding_challenges
(course_id, lesson_id, module_id, title, slug, language, difficulty, marks, passing_score, time_limit, memory_limit, problem, input_desc, output_desc, constraints, sample_input, sample_output, starter_code, is_published, sort_order)
VALUES
(@cid, NULL, NULL, 'Bank Account Class', 'cpp-bank-account-class', 'cpp', 'easy', 10, 50, 5, 128,
'Create a class Account with private fields name (string) and balance (double).
Give it:
- A constructor Account(string n, double b).
- void deposit(double x) that adds x to the balance.
- void withdraw(double x) that subtracts x, but only if the balance is enough.
- void print() that prints exactly:  name: balance  where balance is shown with exactly 2 decimal places.',
'The first line is the account name. The second line is the starting balance.
The following lines are operations: deposit <amount> or withdraw <amount>.
The operations end with the line: print',
'Exactly one line:  <name>: <balance>  with balance formatted to 2 decimal places.',
'name has no spaces. 0 <= balance <= 1,000,000. Amounts are positive numbers.',
'John
1000
deposit 500
withdraw 200
print',
'John: 1300.00',
'#include <iostream>
#include <iomanip>
#include <string>
using namespace std;

// TODO: write the Account class

int main() {
    string name; double bal;
    cin >> name >> bal;
    Account acc(name, bal);
    string op;
    while (cin >> op) {
        if (op == "print") { acc.print(); break; }
        double amt; cin >> amt;
        if (op == "deposit") acc.deposit(amt);
        else acc.withdraw(amt);
    }
    return 0;
}',
1, 1);

SET @ch := LAST_INSERT_ID();
INSERT INTO coding_test_cases (challenge_id, input_data, expected_output, is_visible, sort_order) VALUES
(@ch, 'John
1000
deposit 500
withdraw 200
print', 'John: 1300.00', 1, 1),
(@ch, 'Ana
0
deposit 10000
withdraw 450
withdraw 50
print', 'Ana: 9500.00', 0, 2),
(@ch, 'Bob
750.50
deposit 25
print', 'Bob: 775.50', 0, 3);

-- ----------------------------------------------------------------------------
-- 2. Student Average with Constructor  (Constructors)
-- ----------------------------------------------------------------------------
INSERT INTO coding_challenges
(course_id, lesson_id, module_id, title, slug, language, difficulty, marks, passing_score, time_limit, memory_limit, problem, input_desc, output_desc, constraints, sample_input, sample_output, starter_code, is_published, sort_order)
VALUES
(@cid, NULL, NULL, 'Student Average with Constructor', 'cpp-student-average-constructor', 'cpp', 'easy', 10, 50, 5, 128,
'Create a class Student with a name (string) and three integer marks.
- The constructor Student(string n, int m1, int m2, int m3) stores the name and computes the average of the three marks as a double.
- A method void print() prints exactly:  name: average  where average is shown with exactly 2 decimal places.',
'The first line is the student name. The second line contains three integers separated by spaces.',
'Exactly one line:  <name>: <average>  with 2 decimal places.',
'name has no spaces. Each mark is between 0 and 100.',
'Amina
70 80 90',
'Amina: 80.00',
'#include <iostream>
#include <iomanip>
#include <string>
using namespace std;

// TODO: write the Student class

int main() {
    string n; int a, b, c;
    cin >> n >> a >> b >> c;
    Student s(n, a, b, c);
    s.print();
    return 0;
}',
1, 2);

SET @ch := LAST_INSERT_ID();
INSERT INTO coding_test_cases (challenge_id, input_data, expected_output, is_visible, sort_order) VALUES
(@ch, 'Amina
70 80 90', 'Amina: 80.00', 1, 1),
(@ch, 'Bakari
50 60 75', 'Bakari: 61.67', 0, 2),
(@ch, 'Chidi
100 100 100', 'Chidi: 100.00', 0, 3);

-- ----------------------------------------------------------------------------
-- 3. Rectangle Class  (Encapsulation)
-- ----------------------------------------------------------------------------
INSERT INTO coding_challenges
(course_id, lesson_id, module_id, title, slug, language, difficulty, marks, passing_score, time_limit, memory_limit, problem, input_desc, output_desc, constraints, sample_input, sample_output, starter_code, is_published, sort_order)
VALUES
(@cid, NULL, NULL, 'Rectangle Class (Encapsulation)', 'cpp-rectangle-encapsulation', 'cpp', 'easy', 10, 50, 5, 128,
'Create a class Rectangle with private fields width and height.
- Provide public setters setWidth(int) and setHeight(int) and getters getWidth() and getHeight().
- Provide int area() returning width * height and int perimeter() returning 2 * (width + height).
Keep the fields private so they can only be changed through the setters.',
'One line with two integers: width and height.',
'Two lines:
Area: <area>
Perimeter: <perimeter>',
'1 <= width, height <= 10000.',
'5 3',
'Area: 15
Perimeter: 16',
'#include <iostream>
using namespace std;

// TODO: write the Rectangle class

int main() {
    Rectangle r;
    int w, h; cin >> w >> h;
    r.setWidth(w); r.setHeight(h);
    cout << "Area: " << r.area() << endl;
    cout << "Perimeter: " << r.perimeter() << endl;
    return 0;
}',
1, 3);

SET @ch := LAST_INSERT_ID();
INSERT INTO coding_test_cases (challenge_id, input_data, expected_output, is_visible, sort_order) VALUES
(@ch, '5 3', 'Area: 15
Perimeter: 16', 1, 1),
(@ch, '7 2', 'Area: 14
Perimeter: 18', 0, 2),
(@ch, '10 10', 'Area: 100
Perimeter: 40', 0, 3);

-- ----------------------------------------------------------------------------
-- 4. Vehicle and Car  (Inheritance)
-- ----------------------------------------------------------------------------
INSERT INTO coding_challenges
(course_id, lesson_id, module_id, title, slug, language, difficulty, marks, passing_score, time_limit, memory_limit, problem, input_desc, output_desc, constraints, sample_input, sample_output, starter_code, is_published, sort_order)
VALUES
(@cid, NULL, NULL, 'Vehicle and Car (Inheritance)', 'cpp-vehicle-car-inheritance', 'cpp', 'easy', 10, 50, 5, 128,
'Create a base class Vehicle with a protected double speed.
- The constructor Vehicle(double s) sets the speed.
- A method void show() prints:  Speed: <speed> km/h  with speed shown to 2 decimal places.

Create a derived class Car : public Vehicle that adds a string brand.
- The constructor Car(string b, double s) sets the brand and passes s to the base class.
- Override void show() to print two lines:
Car: <brand>
Speed: <speed> km/h',
'One line: the car brand (no spaces) followed by the speed as a number.',
'Two lines, with the speed formatted to 2 decimal places:
Car: <brand>
Speed: <speed> km/h',
'brand has no spaces. 1 <= speed <= 500.',
'Toyota 120',
'Car: Toyota
Speed: 120.00 km/h',
'#include <iostream>
#include <iomanip>
#include <string>
using namespace std;

// TODO: write the Vehicle and Car classes

int main() {
    string brand; double s;
    cin >> brand >> s;
    Car c(brand, s);
    c.show();
    return 0;
}',
1, 4);

SET @ch := LAST_INSERT_ID();
INSERT INTO coding_test_cases (challenge_id, input_data, expected_output, is_visible, sort_order) VALUES
(@ch, 'Toyota 120', 'Car: Toyota
Speed: 120.00 km/h', 1, 1),
(@ch, 'Mercedes 200', 'Car: Mercedes
Speed: 200.00 km/h', 0, 2),
(@ch, 'Bajaj 60.5', 'Car: Bajaj
Speed: 60.50 km/h', 0, 3);

-- ----------------------------------------------------------------------------
-- 5. Animal Sounds  (Method Overriding)
-- ----------------------------------------------------------------------------
INSERT INTO coding_challenges
(course_id, lesson_id, module_id, title, slug, language, difficulty, marks, passing_score, time_limit, memory_limit, problem, input_desc, output_desc, constraints, sample_input, sample_output, starter_code, is_published, sort_order)
VALUES
(@cid, NULL, NULL, 'Animal Sounds (Method Overriding)', 'cpp-animal-sounds-overriding', 'cpp', 'easy', 10, 50, 5, 128,
'Create a base class Animal with a virtual void speak() that prints:  Animal makes a sound
Create a derived class Dog : public Animal that overrides speak() to print:  Dog barks
Create a derived class Cat : public Animal that overrides speak() to print:  Cat meows

Use a base-class pointer (Animal*) to call the right method depending on the input.',
'One word on a line: dog, cat, or animal.',
'The corresponding sound on one line.',
'The word is exactly dog, cat, or animal.',
'dog',
'Dog barks',
'#include <iostream>
#include <string>
using namespace std;

// TODO: write the Animal, Dog and Cat classes

int main() {
    string type; cin >> type;
    Animal* a;
    if (type == "dog") a = new Dog();
    else if (type == "cat") a = new Cat();
    else a = new Animal();
    a->speak();
    return 0;
}',
1, 5);

SET @ch := LAST_INSERT_ID();
INSERT INTO coding_test_cases (challenge_id, input_data, expected_output, is_visible, sort_order) VALUES
(@ch, 'dog', 'Dog barks', 1, 1),
(@ch, 'cat', 'Cat meows', 0, 2),
(@ch, 'animal', 'Animal makes a sound', 0, 3);

-- ----------------------------------------------------------------------------
-- 6. Function Overloading  (Compile-time Polymorphism)
-- ----------------------------------------------------------------------------
INSERT INTO coding_challenges
(course_id, lesson_id, module_id, title, slug, language, difficulty, marks, passing_score, time_limit, memory_limit, problem, input_desc, output_desc, constraints, sample_input, sample_output, starter_code, is_published, sort_order)
VALUES
(@cid, NULL, NULL, 'Function Overloading (Compile-time Polymorphism)', 'cpp-function-overloading-areas', 'cpp', 'easy', 10, 50, 5, 128,
'Write three overloaded functions all named area:
- int area(int side) returns the area of a square (side * side).
- int area(int w, int h) returns the area of a rectangle (w * h).
- double area(double r) returns the area of a circle using 3.14159 * r * r.

The correct version is chosen automatically based on the number and type of arguments.',
'One line: either  square <side>  ,  rect <w> <h>  , or  circle <radius>',
'One line:  Area: <value>
Square and rectangle areas are integers. The circle area is printed with exactly 2 decimal places.',
'1 <= values <= 10000.',
'square 5',
'Area: 25',
'#include <iostream>
#include <iomanip>
using namespace std;

// TODO: write the three overloaded area functions

int main() {
    string shape; cin >> shape;
    cout << fixed << setprecision(2);
    if (shape == "square") { int s; cin >> s; cout << "Area: " << area(s) << endl; }
    else if (shape == "rect") { int w, h; cin >> w >> h; cout << "Area: " << area(w, h) << endl; }
    else { double r; cin >> r; cout << "Area: " << area(r) << endl; }
    return 0;
}',
1, 6);

SET @ch := LAST_INSERT_ID();
INSERT INTO coding_test_cases (challenge_id, input_data, expected_output, is_visible, sort_order) VALUES
(@ch, 'square 5', 'Area: 25', 1, 1),
(@ch, 'rect 6 7', 'Area: 42', 0, 2),
(@ch, 'circle 2.5', 'Area: 19.63', 0, 3);

-- ----------------------------------------------------------------------------
-- 7. Virtual Functions  (Runtime Polymorphism)
-- ----------------------------------------------------------------------------
INSERT INTO coding_challenges
(course_id, lesson_id, module_id, title, slug, language, difficulty, marks, passing_score, time_limit, memory_limit, problem, input_desc, output_desc, constraints, sample_input, sample_output, starter_code, is_published, sort_order)
VALUES
(@cid, NULL, NULL, 'Virtual Functions (Runtime Polymorphism)', 'cpp-virtual-functions-runtime-polymorphism', 'cpp', 'medium', 15, 50, 5, 128,
'Create a base class Shape with a virtual double area() = 0.
- Create a derived class Circle that takes a radius and returns 3.14159 * r * r.
- Create a derived class Rectangle that takes width and height and returns w * h.

In main, create the right object through a Shape* pointer and print its area.',
'One line: either  circle <radius>  or  rect <width> <height>',
'One line:  Area: <value>  with exactly 2 decimal places.',
'1 <= values <= 10000.',
'circle 3',
'Area: 28.27',
'#include <iostream>
#include <iomanip>
using namespace std;

// TODO: write the Shape, Circle and Rectangle classes

int main() {
    cout << fixed << setprecision(2);
    string type; cin >> type;
    Shape* s;
    if (type == "circle") { double r; cin >> r; s = new Circle(r); }
    else { double w, h; cin >> w >> h; s = new Rectangle(w, h); }
    cout << "Area: " << s->area() << endl;
    return 0;
}',
1, 7);

SET @ch := LAST_INSERT_ID();
INSERT INTO coding_test_cases (challenge_id, input_data, expected_output, is_visible, sort_order) VALUES
(@ch, 'circle 3', 'Area: 28.27', 1, 1),
(@ch, 'circle 1', 'Area: 3.14', 0, 2),
(@ch, 'rect 7 3', 'Area: 21.00', 0, 3);

-- ----------------------------------------------------------------------------
-- 8. Abstract Class  (Abstraction)
-- ----------------------------------------------------------------------------
INSERT INTO coding_challenges
(course_id, lesson_id, module_id, title, slug, language, difficulty, marks, passing_score, time_limit, memory_limit, problem, input_desc, output_desc, constraints, sample_input, sample_output, starter_code, is_published, sort_order)
VALUES
(@cid, NULL, NULL, 'Abstract Class with Pure Virtual Function', 'cpp-abstract-class-pure-virtual', 'cpp', 'medium', 15, 50, 5, 128,
'Create an abstract base class Shape with a pure virtual double area() = 0.
- Create a derived class Triangle that takes a base and a height and returns 0.5 * base * height.
- Create a derived class Rectangle that takes width and height and returns width * height.

Because Shape has a pure virtual function, it can never be instantiated directly. Use a Shape* pointer to the derived object.',
'One line: either  triangle <base> <height>  or  rect <width> <height>',
'One line:  Area: <value>  with exactly 2 decimal places.',
'1 <= values <= 10000.',
'triangle 4 5',
'Area: 10.00',
'#include <iostream>
#include <iomanip>
using namespace std;

// TODO: write the abstract Shape class, Triangle and Rectangle

int main() {
    cout << fixed << setprecision(2);
    string type; cin >> type;
    Shape* s;
    if (type == "triangle") { double b, h; cin >> b >> h; s = new Triangle(b, h); }
    else { double w, h; cin >> w >> h; s = new Rectangle(w, h); }
    cout << "Area: " << s->area() << endl;
    return 0;
}',
1, 8);

SET @ch := LAST_INSERT_ID();
INSERT INTO coding_test_cases (challenge_id, input_data, expected_output, is_visible, sort_order) VALUES
(@ch, 'triangle 4 5', 'Area: 10.00', 1, 1),
(@ch, 'rect 5 7', 'Area: 35.00', 0, 2),
(@ch, 'triangle 6 10', 'Area: 30.00', 0, 3);

-- ----------------------------------------------------------------------------
-- 9. Complex Numbers  (Operator Overloading)
-- ----------------------------------------------------------------------------
INSERT INTO coding_challenges
(course_id, lesson_id, module_id, title, slug, language, difficulty, marks, passing_score, time_limit, memory_limit, problem, input_desc, output_desc, constraints, sample_input, sample_output, starter_code, is_published, sort_order)
VALUES
(@cid, NULL, NULL, 'Complex Numbers (Operator Overloading)', 'cpp-complex-operator-overloading', 'cpp', 'medium', 15, 50, 5, 128,
'Create a class Complex with private double fields real and imag.
- Provide a constructor Complex(double r, double i).
- Overload operator+ so that adding two Complex values adds their real and imag parts.
- Overload operator<< so that the sum prints as:  <real> <sign> <imag>i  where sign is + when imag is zero or positive and - when imag is negative (in that case print the absolute value of imag).

Example: Complex(4, 6) prints as  4 + 6i   and Complex(4, -1) prints as  4 - 1i',
'One line with four integers: a b c d  meaning (a + bi) and (c + di).',
'One line:  Sum: <real> <sign> <imag>i  using the rules above.',
'All values are integers between -100 and 100.',
'1 2 3 4',
'Sum: 4 + 6i',
'#include <iostream>
using namespace std;

// TODO: write the Complex class and overload + and <<

int main() {
    double a, b, c, d;
    cin >> a >> b >> c >> d;
    Complex x(a, b), y(c, d);
    cout << "Sum: " << (x + y) << endl;
    return 0;
}',
1, 9);

SET @ch := LAST_INSERT_ID();
INSERT INTO coding_test_cases (challenge_id, input_data, expected_output, is_visible, sort_order) VALUES
(@ch, '1 2 3 4', 'Sum: 4 + 6i', 1, 1),
(@ch, '5 2 -1 -3', 'Sum: 4 - 1i', 0, 2),
(@ch, '0 0 4 9', 'Sum: 4 + 9i', 0, 3);

-- ----------------------------------------------------------------------------
-- 10. Employee Static Counter  (Static Members)
-- ----------------------------------------------------------------------------
INSERT INTO coding_challenges
(course_id, lesson_id, module_id, title, slug, language, difficulty, marks, passing_score, time_limit, memory_limit, problem, input_desc, output_desc, constraints, sample_input, sample_output, starter_code, is_published, sort_order)
VALUES
(@cid, NULL, NULL, 'Employee Static Counter', 'cpp-employee-static-counter', 'cpp', 'medium', 15, 50, 5, 128,
'Create a class Employee with:
- A private string name.
- A private static int count that keeps how many Employee objects have been created.
- A private int id.
- A constructor Employee(string n) that increments count and assigns id = count.
- A method void print() that prints:  ID <id>: <name>

Because count is static, it is shared by all Employee objects.',
'The first line is the number of employees n. The next n lines each contain one employee name.',
'n lines of  ID <id>: <name>  followed by one final line:  Total Employees: <n>',
'1 <= n <= 100. Names have no spaces.',
'2
Alice
Bob',
'ID 1: Alice
ID 2: Bob
Total Employees: 2',
'#include <iostream>
#include <string>
using namespace std;

// TODO: write the Employee class with a static counter

int main() {
    int n; cin >> n;
    Employee* list[100];
    for (int i = 0; i < n; i++) {
        string name; cin >> name;
        list[i] = new Employee(name);
    }
    for (int i = 0; i < n; i++) list[i]->print();
    cout << "Total Employees: " << Employee::count << endl;
    return 0;
}',
1, 10);

SET @ch := LAST_INSERT_ID();
INSERT INTO coding_test_cases (challenge_id, input_data, expected_output, is_visible, sort_order) VALUES
(@ch, '2
Alice
Bob', 'ID 1: Alice
ID 2: Bob
Total Employees: 2', 1, 1),
(@ch, '1
Zawadi', 'ID 1: Zawadi
Total Employees: 1', 0, 2),
(@ch, '3
Tom
Dick
Harry', 'ID 1: Tom
ID 2: Dick
ID 3: Harry
Total Employees: 3', 0, 3);

-- ============================================================================
-- Done. Verify with:
--   SELECT id, title, difficulty FROM coding_challenges ORDER BY sort_order;
--   SELECT c.title, COUNT(t.id) FROM coding_challenges c
--     LEFT JOIN coding_test_cases t ON t.challenge_id = c.id
--     GROUP BY c.id ORDER BY c.sort_order;
-- ============================================================================
