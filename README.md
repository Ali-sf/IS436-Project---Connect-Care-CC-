<!-- README.md -->
# Connect Care User Guide Documentation

## Table of Contents
* [Introduction](#introduction)
* [Features](#features)
* [Project Plan](#plan)
* [Website](#website)
* [Contact Information](#contacts)

## Introduction

### What is Connect Care (CC)?:
- ConnectCare is a Healthcare Web Platform where doctors and patients can securely access their medical records, schedule appointments, manage appointments and contact each other with any questions
- Based on the agile framework

### Goal: 
- The purpose of our website is to create a web platform that can help doctors and patients easily access any health related data. 
- The design of the website is to help patients securely access their medical records, view any medical bills the individual owes, manage their medical appointments, and communicate with doctors without having to go in person to the doctor’s office to do so. 
- Our main objectives are to provide a website that will provide a secure interface for patients, doctors, and administrators, ensuring confidentiality, integrity, availability, and ease of use, all in accordance with HIPAA and other government regulations.

## Features
* Login Features for Users
  * Registration of new accounts/users
  * Separate homepages depending on what type of user
* Messaging Capabilities between users
* View Appointments
* View Medical Records

## Plan

### Planning Phase
- **System Request Requirements**
  - *Business Needs*
    - The healthcare industry requires a secure and efficient way for doctors, patients, and administrators to access health-related data online. 
    - The goal of the platform is to allow patients to securely access medical records, manage bills, schedule appointments, and communicate with their healthcare providers without needing to visit in person. This is particularly important in situations where in-person consultations are not feasible or necessary. 
  - This system will make healthcare convenient for both patients and healthcare workers ensuring a smooth process for both parties.
  - *Business Requirements*
    - *The platform must meet the following requirements:*
      - Secure User Authentication:
        - All parties must be able to securely access their details without the risk of outsiders accessing their data and personal information. This part is extremely important because if the website cannot be trusted then there is no purpose in the project.

      - Medical Records Management:
        - Patients and doctors can view their medical records with ease. With one login they can access important medical records for each appointment.

      - Billing and Payment Portal:
        - Patients can view outstanding medical bills and make payments online or in their next appointment.

  - *Business Values*
    - *This platform will provide the following value to the organization:*
      - Improved Patient Access:
        - Patients will have real-time access to their medical records and bills, reducing the need for follow-ups and physical visits.

      - Enhanced Efficiency for Healthcare Providers:
        - By streamlining the scheduling and communication processes, doctors can manage their appointments and patient data more efficiently, leading to better patient care.
        - This whole process will allow for less stress between both parties hopefully leading to higher quality care.

      - Compliance with Government Regulations (HIPAA):
        - The platform will follow all necessary security protocols and government regulations, ensuring patient data confidentiality, integrity, and availability.
      - Increased Satisfaction and Convenience:
        - The purpose of this platform is to be convenient while also being satisfying to use for both parties. If both parties find the web application to be convenient and easy to use then it can be considered a success.

  - *Special Issues or Constraints*
    - HIPAA Compliance:
      - The system must adhere to the Health Insurance Portability and Accountability Act (HIPAA). If the web application wants to run for a long time then it will need to follow guidelines in order to stay running.
    - Data Security and Encryption:
      - All medical records and communications must be encrypted to protect against unauthorized access.
    - Scalability:
      - The platform must be able to scale based on user demand. If the web application ends up being a success then it will need the ability to be scalable in order to match that demand otherwise there is no purpose in this project.
    - Cross-Platform Compatibility:
      - The website must be accessible on various devices, including desktops, tablets, and smartphones.

- **Feasibility Analysis**
  1. ***Technical Feasibility***
    - User Familiarity:
      - The users and analysts have a basic understanding of the website and its many features as we will design it to be simple to use.
    - Have we used it before?:
        - No, the site will be unique, however it will contain similar features to other medical websites. So it won’t be too new.
    - Project Size:
      - All parties will have a hand in contribution to the size of the project. Including Doctors, Patients and a team of lawyers to contribute to the legal uses of the site.
    - Compatibility with existing systems and degree of integration required:
      - The site will be compatible with any system and will not require a huge degree of integration into the user’s hardware.
  2. ***Economic Feasibility***
  - *Initial Investment*
    - $285,000 cost for product development.
  - *Expected Revenue*
    - $175,000 within the first year with a 70% annual growth rate projected over the next two years.
  - *Break-Even Point*
    - The break-even point will be achieved after 6 months.
  - *Net-Present Value*
    - Initially it will be -145,000 but will have an increase of 4% within the next year.

- Cost Benefit Analysis
<!--Table-->
| Year     | Development Cost     | Operational Cost     |  Total Costs     | Benefits     | Net Benefits     |
| -------- | -------------------- | -------------------- |  --------------- | ------------ | ---------------- |
| 2024     | 285000               | 35000                | 175000           | 175000       | -145000          |
| 2025     | 0                    | 90000                | 90000            | 290000       | 200000           |
| 2026     | 0                    | 115000               | 115000           | 375000       | 260000           |

### Design Phase
- Use Case analysis performed
- Context Diagram, Level 0 and Level N Diagrams created
- Use Cases updated
- Defining of Processes, Entities, Data Stores, & Data Flows

### Implementation Phase
- Created page for users to login to website or create an account
- Made it possible so that user is able to choose between Doctor or Patient type user
- Made it possible so that user home page changes depending on user tupe
- Made it possible so that user is able to schedule appointments and view appointments
- Made it possible so that user is able so that patient type users are able to view their own medical records
- Made it possible for users to message one another through website

### Testing Phase
- Made sure that HTML pages lead to intended places
- Made sure that SQL databases held correct data and tables
- Made sure that user data is encryped when placed inside SQL tables
- Made sure users are created and stored as intended

### Deployment Phase
- Able to launch website out of HTML and Docker
- SQL holds all of the users data

## Website
[Connect Care](https://swe.umbc.edu/~nm03056/ConnectCare_Project/)

## Contacts
### **Team Name: Connect Care (CC)**
Contact Email: judek1@umbc.edu

### **Team Members:**
#### Jude Kim
- Role: Project Manager
- Email: judek1@umbc.edu
#### Ali Nawaz
- Role: Backend Developer
- Email: anawaz1@umbc.edu
#### Michael He
- Role: Database Administrator
- Email: nm03056@umbc.edu
#### Austin Phasukyued
- Role: Frontend Developer
- Email: aphasuk1@umbc.edu
#### Kalkidan Letibelu
- Role: Quality Assurance
- Email: l78@umbc.edu
#### Ben Lubinski
- Role: Compliance Officer
- Email: blubins1@umbc.edu