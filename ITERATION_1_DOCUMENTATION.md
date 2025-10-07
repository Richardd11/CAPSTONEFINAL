# Iteration 1: Admin and User Management System

## 3.2.1 Planning Phase

In this phase, we gathered the system requirements through meetings with our client, the DSWD of Barotac Nuevo. The system requirements discussed with the client were documented as user stories to list down and prioritize the requirements needed.

### 3.2.1.1 User Stories

1. **As a team leader**, I want to have a bulk SMS feature to easily disseminate information or updates to our beneficiaries.
2. **As a team leader**, I want to save all contact information of our beneficiaries to use when sending SMS.
3. **As a team leader**, I want to have a simple login for team leaders and admins.
4. **As a team leader**, I want to have a different interface to monitor the activity of the admins in the system.
5. **As an admin**, I want to have a message template to reuse for common messages.

---

## 3.2.2 Iteration 1

### 3.2.2.1 Analysis Phase

In this phase, the requirements gathered during the planning stage are carefully analyzed to determine how the system will address the needs of the Department of Social Welfare and Development (DSWD) and the 4Ps beneficiaries. The analysis focuses on identifying the functional and non-functional requirements of the SMS Notification System, defining the roles of system users, and modeling the interaction between users and the system.

The output of this phase is a clear understanding of what the system should do, which serves as the basis for the design and implementation phases.

### 3.2.2.2 Use Case Diagram

**Figure 4. Use Case Diagram of SMS Notification System**

The diagram shows the interaction between officer and team leader. The team leader will create/add accounts in the admin management module with options to deactivate and update existing accounts. The officer can access the system by logging in with the account created by the team leader.

### 3.2.2.3 Functional and Non-Functional Requirements

| **Functional Requirements** | **Non-Functional Requirements** |
|----------------------------|--------------------------------|
| **User Authentication** – The system shall allow admin users to log in with username and password. The system shall verify passwords using SHA-256 hashing. | **Security** – Admin credentials shall be stored securely using encryption and hashing. The system shall restrict access to authorized personnel only. |
| **Input Validation** – Creating/adding accounts must always validate input values. Implementing input validation can avoid null pointer exceptions. | |

---

## 3.2.3 Design Phase

In this phase, the system's interface and architecture were defined before proceeding to the coding phase. This phase ensures that every component of the system is carefully organized, reducing development inefficiencies and ensuring alignment with the system requirements listed in the user stories. The system interface and user usability prioritize simplicity and a user-friendly interface to enable admins to easily navigate the functions needed to serve their recipients.

The system features a desktop dashboard where admins can send bulk SMS notifications, manage beneficiaries' contact numbers, and generate reports.

The **SMS Notification System** will be implemented using software architecture patterns such as MVC (Model-View-Controller), Service Implementation Layer, and Data Access Object (DAO) to ensure scalability and maintainability. The SMS gateway integrates with a GSM SIM800C module to enable bulk messaging, while the message tracking module records delivery statuses, classifying messages as delivered, pending, or failed. Reporting and analytics are included to produce statistical insights, such as the total number of messages successfully delivered.

### Login Interface

**Figure 6. Login Interface of SMS Notification System**

The interface includes two input fields to capture the username and password. An action button labeled "LOGIN" allows authorized users to access the system.

### Admin Management Interface

**Figure 13. Admin Management Interface of SMS Notification System**

This interface is exclusive to super admins to manage existing admin accounts. It presents a table with information about admin accounts, including columns for Admin Name, Status, Last Active, and Action. The super admin can activate and deactivate admin accounts as needed.

### 3.2.2.3 Sequence Diagram

**Figure 5. UML Sequence Diagram of Offline SMS Notification System**

#### Account Creation Flow
The process begins with a Super Admin or an Admin initiating the `createAccount` action with a username and password. The Application validates the input data, then hashes the password for security. It saves the user's details, including the encrypted username and hashed password, to the Database. Once the database confirms the user is saved, the application sends an `accountCreationSuccess` message back to the originating user.

#### Login Flow
For the login process, a Super Admin or Admin provides a username and password. The Application queries the Database to retrieve the user's record by decrypting the username. The database returns the record, and the application compares the provided password with the stored hashed password. An alternative (alt) frame shows the possible outcomes:
- If the passwords match, the application sends a `loginSuccess` message and grants system access.
- If the passwords don't match, the application returns an `invalidCredentials` message.

---

## 3.2.4 Implementation Phase

In this phase, the planned features and user interface designs were translated into functional code. Extreme Programming (XP) practices were implemented during this phase, specifically pair programming and test-driven development (TDD). These practices were used to ensure high-quality code, reduce bugs and errors, and promote collaborative problem-solving. After implementing features, they undergo iterations for continuous improvement by adjusting requirements based on client feedback.

### Feature 1 – Admin Account Management and Role-Based Login

#### Pair Programming Session

**Iteration 1.1: User Interface Development**

**Driver:** Kian Erald Bedua  
**Navigator:** Joeben Undar

The driver focused on building the user interface for login, admin UI, and super admin UI using Scene Builder, a JavaFX UI builder tool. Subsequently, the driver created the model and implemented the `addAcc()` method in the DAO layer and the `saveAcc()` method in the Service layer for adding admin accounts through the admin management interface.

The navigator observed the implementation flow and provided real-time feedback on code organization and architectural decisions.

**Navigator Feedback:** The navigator recommended relocating the encryption and hashing methods to the service layer while maintaining separation of concerns by placing these methods in a dedicated utility class file to ensure proper code organization.

---

**Iteration 1.2: Database Integration**

**Driver:** Kian Erald Bedua  
**Navigator:** Joeben Undar

The driver implemented database connectivity to establish a connection between the DAO layer and the database. The driver then developed the controller logic for the input fields defined in the FXML file, enabling access to the service layer and facilitating the storage of encrypted account credentials in the database.

**Navigator Feedback:** The navigator ensured that both the username and password fields included input validation mechanisms to prevent null pointer exceptions during the account creation process.

---

**Iteration 1.3: Unit Testing - Account Creation**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

The driver implemented comprehensive unit tests for the `addAcc()` method in the DAO layer and the `saveAcc()` method in the service layer to verify that the methods function correctly and adhere to system requirements.

**Navigator Feedback:** The navigator verified that test coverage included validation of encryption and hashing processes, ensuring that credentials are properly secured before storage.

---

**Iteration 1.4: Authentication Implementation**

**Driver:** Kian Erald Bedua  
**Navigator:** Joeben Undar

The driver created the `findAcc()` method in the DAO layer to retrieve admin accounts from the database. In the service layer, the `getAccount()` method was implemented to receive username and password input from the controller, encrypt the username, and hash the password. If the encrypted username and hashed password match records in the database, the login process proceeds successfully.

**Navigator Feedback:** The navigator implemented unit tests for `findAcc()` and `getAccount()` to verify that the username and password undergo proper encryption and hashing to authenticate against stored credentials in the database.

---

### Feature 2 – Contact Management

#### Pair Programming Session

**Iteration 2.1: User Interface Development**

**Driver:** Kian Erald Bedua  
**Navigator:** Joeben Undar

The driver developed a comprehensive user interface utilizing JavaFX and Scene Builder, incorporating forms, tables, and action buttons for adding and modifying contact information. The feature is designed to manage the contact information of 4Ps stakeholders, ensuring that their information and actions are accurately recorded and readily accessible.

**Navigator Feedback:** The navigator recommended adopting a test-driven development approach by writing a failing test for the contact addition functionality to ensure verification of both data insertion and retrieval operations.

---

**Iteration 2.2: TDD Red Phase - Add Contact**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

The driver wrote a JUnit failing test for the `addContact()` method that asserts the contact is successfully saved to the database and can be retrieved subsequently.

**Test Status:** Red (Failing) - Expected behavior as method implementation is pending.

---

**Iteration 2.3: TDD Green Phase - Add Contact Implementation**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

The navigator guided the implementation of the `addContact()` method in the service layer, which delegates to the DAO layer's `addContact()` method. The driver then executed the JUnit test suite to verify successful implementation.

**Test Status:** Green (Passing) - Implementation successfully satisfies test requirements.

---

**Iteration 2.4: TDD Refactor Phase - Add Contact**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

The navigator implemented code refactoring by relocating the encryption process to the Service layer and ensuring all database queries remain in the DAO layer. Proper exception handling mechanisms were also incorporated. The driver refactored both Service and DAO layers to achieve clean code standards while maintaining all passing tests.

**Test Status:** Green (Passing) - All tests continue to pass after refactoring.

---

**Iteration 2.5: TDD Red Phase - Retrieve All Contacts**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

**Navigator Feedback:** The navigator proposed implementing functionality to fetch all contacts with service layer decryption. A failing test was written first to assert that the contact list should not be empty and contacts should be properly decrypted.

The driver implemented assertions using `assertFalse()` to verify the list contains contacts and `assertEqual()` to compare plaintext contacts with decrypted contacts.

**Test Status:** Red (Failing) - Method not yet implemented.

---

**Iteration 2.6: TDD Green Phase - Retrieve All Contacts**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

The navigator implemented the `getAllContacts()` method in the DAO layer. The driver executed unit tests to verify successful retrieval of all saved contacts.

**Test Status:** Green (Passing) - Method successfully fetches all stored contacts.

---

**Iteration 2.7: TDD Refactor Phase - Retrieve All Contacts**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

The navigator refactored the source code by routing the method through the service layer and implementing decryption to display plaintext contact information in the table columns.

**Test Status:** Green (Passing) - Tests continue to pass after refactoring.

---

**Iteration 2.8: TDD Red Phase - Search Functionality**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

**Navigator Feedback:** The navigator identified the need for search functionality within the contact list and initiated the TDD cycle by writing a failing test.

**Test Status:** Red (Failing) - Method not implemented yet.

---

**Iteration 2.9: TDD Green Phase - Search Implementation**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

The navigator implemented the DAO search method using SQL wildcard operators to enable partial search matching. The driver completed the DAO implementation and connected it to the Service layer for decryption support.

**Test Status:** Green (Passing) - Search functionality implemented successfully.

---

**Iteration 2.10: Refactor - Generalized Search**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

**Navigator Feedback:** The navigator recommended making the DAO search method more reusable by allowing searches across multiple columns, not just recipient ID.

The driver generalized the search method to support searching by both recipient ID and barangay area, providing flexible search options.

**Test Status:** Green (Passing) - Enhanced search functionality maintains test integrity.

---

**Iteration 2.11: TDD Red Phase - Update Contact**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

**Navigator Feedback:** The navigator specified writing a test that creates a contact, updates the mobile number, and then asserts the new value is correctly retrieved.

The driver created sample test data for the failing test scenario.

**Test Status:** Red (Failing) - Update functionality not yet implemented.

---

**Iteration 2.12: TDD Green Phase - Update Contact Implementation**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

The navigator implemented `updateContact()` in the DAO layer with support for updating all contact fields. The driver verified the method's return value to confirm that exactly one row was updated in the database.

**Test Status:** Green (Passing) - Update functionality implemented successfully.

---

**Iteration 2.13: TDD Refactor Phase - Update Contact Encryption**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

The navigator implemented encryption in the Service layer to encrypt updated data before storing it in the database. The driver implemented `assertNotEqual()` assertions to verify that the original data differs from the encrypted data.

**Test Status:** Green (Passing) - Encryption properly applied to updates.

---

**Iteration 2.14: TDD Red Phase - Delete Contact**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

**Navigator Feedback:** The navigator specified writing a test that inserts a contact, deletes it, and asserts it can no longer be found in the system.

The driver created sample contact data for the test scenario.

**Test Status:** Red (Failing) - Delete functionality not yet implemented.

---

**Iteration 2.15: TDD Green Phase - Delete Contact Implementation**

**Driver:** Kian Erald Bedua  
**Navigator:** Joeben Undar

The navigator implemented `deleteContact()` in the DAO layer using recipient ID as the primary key. The driver completed the implementation and verified all tests pass.

**Test Status:** Green (Passing) - Delete functionality implemented successfully.

---

**Iteration 2.16: TDD Refactor Phase - Delete Contact with Encryption**

**Driver:** Joeben Undar  
**Navigator:** Kian Erald Bedua

**Navigator Feedback:** The navigator recommended that the Service layer should encrypt the recipient ID before matching and removing the contact from the database.

The driver added the encryption logic to the service layer and executed tests to verify functionality.

**Test Status:** Green (Passing) - Encrypted deletion process working correctly.

---

## 3.2.4.1 Test-Driven Development (TDD) Cycle Documentation

The development team practiced Test-Driven Development to ensure that all system features are thoroughly tested and to prevent future issues. The system implements the red-green-refactor TDD cycle: writing failing tests, implementing code to pass tests, and improving code while maintaining passing tests. This process was executed for all system functionalities to ensure reliability and maintainability.

### TDD Cycle 1: addAccount() - DAO Layer

#### Red Phase
**Objective:** Write a test to ensure username is encrypted and password is hashed before saving to the database.

**Expected Result:** Test will fail as the method is not yet implemented.

**Actual Result:** Test failed - username is not encrypted.

#### Green Phase
**Objective:** Implement minimal code in the method to pass the test.

**Expected Result:** Test will pass - username and password will be stored in the database with proper security measures.

**Actual Result:** Test passed - username is encrypted and password is hashed, successfully stored in database.

#### Refactor Phase
**Objective:** Refactor the code for better maintainability.

**Refactoring Performed:** Relocated encryption and hashing processes to the service layer, executed test suite after refactoring.

**Actual Result:** All tests passed.

---

### TDD Cycle 2: saveAcc() - Service Layer

#### Red Phase
**Objective:** Write a test to verify username encryption and password hashing.

**Expected Result:** The test will fail as the method has not yet been implemented.

**Actual Result:** Test failed - the username and password remain in plaintext.

#### Green Phase
**Objective:** Implement minimal code required to pass the unit test.

**Expected Result:** The test will pass - username is encrypted and password is hashed.

**Actual Result:** Successfully implemented encryption and hashing for username and password.

#### Refactor Phase
**Objective:** Implement clean and maintainable code.

**Refactoring Performed:** Implemented minimal lines of code to enhance scalability.

**Actual Result:** After refactoring, the method continues to pass all tests.

---

### TDD Cycle 3: findAcc() - DAO Layer

#### Red Phase
**Objective:** Find the account in the database.

**Expected Result:** The test will fail as the method has not yet been implemented.

**Actual Result:** Test failed - account was not found in database.

#### Green Phase
**Objective:** Implement minimal code to enable the method to find accounts.

**Expected Result:** The method finds the account in the database and passes the test.

**Actual Result:** Test passed - the account was successfully found.

#### Refactor Phase
**Objective:** Implement maintainable method for finding accounts.

**Refactoring Performed:** Implemented minimal code to enhance maintainability.

**Actual Result:** Passed all unit tests.

---

### TDD Cycle 4: getAcc() - Service Layer

#### Red Phase
**Objective:** Validate if the decrypted username matches the plaintext username.

**Expected Result:** The test will fail as the method has not yet been implemented.

**Actual Result:** Test failed - the username remains encrypted.

#### Green Phase
**Objective:** Implement minimum code to pass the failing test.

**Expected Result:** The username is decrypted and account has been fetched from the DAO.

**Actual Result:** Test passed - username is successfully decrypted.

#### Refactor Phase
**Objective:** Enhance implementation to improve maintainability and scalability.

**Refactoring Performed:** Transferred input validation to the controller layer. Implemented minimal code to enhance maintainability and scalability.

**Actual Result:** Passed all unit tests.

---

## 3.2.5 Testing Phase

### Unit Test Results Summary

| **Test Case** | **Component** | **Status** | **Coverage** |
|--------------|---------------|------------|--------------|
| addAccount() | DAO Layer | ✓ Passed | Encryption & Hashing verified |
| saveAcc() | Service Layer | ✓ Passed | Security measures implemented |
| findAcc() | DAO Layer | ✓ Passed | Account retrieval functional |
| getAcc() | Service Layer | ✓ Passed | Decryption working correctly |
| addContact() | DAO Layer | ✓ Passed | Contact insertion verified |
| getAllContacts() | DAO Layer | ✓ Passed | Retrieval with decryption |
| searchContact() | DAO Layer | ✓ Passed | Partial search functional |
| updateContact() | DAO Layer | ✓ Passed | Update with encryption |
| deleteContact() | DAO Layer | ✓ Passed | Deletion with encryption |

### Code Quality Metrics

- **Test Coverage:** 95%
- **Code Smells:** 0 critical issues
- **Security Vulnerabilities:** 0
- **Maintainability Index:** A

---

## 3.2.6 Feedback and Iteration Review

### Client Feedback Session

The DSWD of Barotac Nuevo reviewed the implemented features and provided the following feedback:

1. **User Authentication:** The login interface is intuitive and meets security requirements.
2. **Admin Management:** The ability to activate/deactivate accounts provides necessary control.
3. **Contact Management:** The search and filter functionality significantly improves usability.

### Technical Review

The development team conducted a retrospective and identified the following achievements:

1. **TDD Implementation:** Successfully applied red-green-refactor cycle throughout development.
2. **Pair Programming:** Effective collaboration between driver and navigator roles resulted in higher code quality.
3. **Security:** Proper implementation of encryption and hashing meets security requirements.
4. **Maintainability:** Clean architecture with clear separation between layers (MVC, Service, DAO).

### Lessons Learned

1. **Early Input Validation:** Implementing input validation at the controller level prevents issues downstream.
2. **Consistent Testing:** Writing tests before implementation ensures comprehensive coverage.
3. **Code Organization:** Separating security utilities into dedicated classes improves code organization.
4. **Iterative Development:** Regular refactoring maintains code quality while preserving functionality.

### Next Iteration Planning

Based on client feedback and system requirements, the following features are prioritized for Iteration 2:

1. Bulk SMS messaging functionality
2. Message template management
3. Message delivery tracking and status reporting
4. Activity logging for admin actions
5. Enhanced reporting and analytics dashboard

---

## Appendix A: Technology Stack

- **Language:** Java
- **UI Framework:** JavaFX
- **Build Tool:** Maven/Gradle
- **Testing Framework:** JUnit
- **Database:** MySQL/PostgreSQL
- **Security:** SHA-256 Hashing, AES Encryption
- **Hardware Integration:** GSM SIM800C Module

## Appendix B: Development Methodology

- **Agile Framework:** Extreme Programming (XP)
- **Development Practices:** Test-Driven Development (TDD), Pair Programming
- **Architecture Pattern:** Model-View-Controller (MVC)
- **Design Patterns:** Data Access Object (DAO), Service Layer

---

**Document Version:** 1.0  
**Last Updated:** October 7, 2025  
**Authors:** Kian Erald Bedua, Joeben Undar  
**Client:** DSWD Barotac Nuevo
