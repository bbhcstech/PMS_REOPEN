<?php

namespace App\Services;

class LetterTemplateService
{
    /**
     * Get all IT Industry Letter Categories and Templates.
     */
    public static function getCategories(): array
    {
        return [
            'apology' => [
                'name' => 'Apology Letters (IT & Workplace)',
                'icon' => 'bx-message-square-error',
                'badge' => 'Workplace & Leaves',
                'templates' => [
                    'apology_leave' => [
                        'name' => 'Apology Letter Regarding Leave',
                        'title' => 'Apology Letter Regarding Leave',
                        'subject' => 'Apology Letter Regarding [Leave Type] Leave',
                        'description' => 'Standard employee apology and regularization for leave of absence.',
                        'content' => "Subject: Apology Letter Regarding [Leave Type] Leave\n\nDear HR Team,\n\nI sincerely apologize for the inconvenience caused due to my leave from [Start Date] to [End Date]. I understand that my absence may have affected work planning and team coordination.\n\nThe reason for my leave was [briefly explain the reason]. I assure you that I will take the necessary steps to avoid similar inconvenience in the future and will coordinate my pending responsibilities promptly.\n\nI kindly request you to consider this apology and accept my explanation.\n\nThank you for your understanding.\n\nSincerely,\n[Employee Name]\n[Designation]\n[Department / Team]\n[Employee ID]",
                    ],
                    'apology_late' => [
                        'name' => 'Apology for Late Arrival / Attendance Delay',
                        'title' => 'Apology for Late Arrival & Attendance Discrepancy',
                        'subject' => 'Apology Letter for Late Arrival on [Date]',
                        'description' => 'Formal apology letter explaining late clock-in or shift delay.',
                        'content' => "Subject: Apology Letter for Late Arrival on [Date]\n\nDear Management & HR Team,\n\nI am writing to formally apologize for my late arrival to the office on [Date]. Due to [unforeseen traffic congestion / medical emergency / public transit failure], I was unable to report to duty by my scheduled shift timing.\n\nI fully recognize the significance of punctuality in maintaining our sprint workflow and client delivery timelines. I have updated my reporting manager and have stayed beyond my regular work hours to ensure all assigned engineering tasks were completed.\n\nI have taken precautionary measures to prevent any reoccurrence of this delay. Thank you for your patience and understanding.\n\nSincerely,\n[Employee Name]\n[Designation]\n[Department / Team]\n[Employee ID]",
                    ],
                    'apology_sprint_delay' => [
                        'name' => 'Apology for Milestone / Sprint Delivery Delay',
                        'title' => 'Apology for Project Milestone & Sprint Delay',
                        'subject' => 'Apology & Remediation Plan Regarding Sprint Delivery Delay on [Project Name]',
                        'description' => 'Engineering team apology and remediation plan for a delayed release or feature milestone.',
                        'content' => "Subject: Apology & Remediation Plan Regarding Sprint Delivery Delay on [Project Name]\n\nDear Project Manager & Technical Leadership,\n\nI am writing to formally apologize for the delay in completing the assigned deliverables for [Milestone / Sprint Name] under project [Project Name], originally scheduled for completion on [Due Date].\n\nDuring the final integration phase, we encountered unexpected [third-party API breaking changes / complex database migration latency / critical edge-case defects] requiring comprehensive refactoring to safeguard data integrity and security.\n\nOur engineering team has implemented a recovery sprint schedule with additional peer reviews. The fully validated release will be deployed to UAT staging by [New Target Date].\n\nWe deeply regret any disruption caused to the client roadmap and appreciate your continued guidance.\n\nSincerely,\n[Employee Name]\n[Designation - Tech Lead / Senior Developer]\n[Project Name]\n[Employee ID]",
                    ],
                    'apology_outage_bug' => [
                        'name' => 'Apology for Production Bug / System Incident',
                        'title' => 'Incident Post-Mortem & Apology for Production Outage',
                        'subject' => 'Post-Incident Apology & Corrective Action Summary for [Incident / Service Name]',
                        'description' => 'Formal apology and root-cause accountability letter for production downtime or software bug.',
                        'content' => "Subject: Post-Incident Apology & Corrective Action Summary for [Incident / Service Name]\n\nDear Technical Leadership & Stakeholders,\n\nI am submitting this formal communication to express our sincere apologies for the production defect and subsequent service degradation experienced on [Date] between [Start Time] and [Resolved Time].\n\nA thorough Root Cause Analysis (RCA) revealed that an edge-case configuration regression occurred during the deployment of build [Build Version]. The hotfix has been verified and deployed, successfully restoring complete system telemetry and normal service latency.\n\nTo prevent any similar recurrence, we have added automated end-to-end integration tests and updated our CI/CD canary deployment gates.\n\nWe appreciate your support and remain committed to zero-downtime engineering excellence.\n\nSincerely,\n[Employee Name]\n[Designation - Lead DevOps / Backend Engineer]\n[Engineering Team]\n[Company Name]",
                    ],
                    'apology_policy_violation' => [
                        'name' => 'Apology for Policy / Security Guideline Non-Compliance',
                        'title' => 'Apology for Workplace / Security Policy Oversight',
                        'subject' => 'Formal Apology Regarding IT Policy Non-Compliance Oversight',
                        'description' => 'Apology letter addressing inadvertent policy, VPN, or hardware protocol oversight.',
                        'content' => "Subject: Formal Apology Regarding IT Policy Non-Compliance Oversight\n\nDear HR & Information Security Committee,\n\nI am writing to submit my sincere apology regarding the policy non-compliance incident noted on [Date], concerning [briefly mention policy, e.g., unauthorized software installation / USB security protocol / delayed NDA submission].\n\nThis oversight was completely inadvertent and occurred without malicious intent. Upon notification, I immediately rectified the situation in direct consultation with the IT Support and InfoSec teams.\n\nI have re-reviewed our Corporate IT Governance and Information Security Handbook. I reaffirm my commitment to maintaining the highest security and compliance standards at all times.\n\nThank you for your understanding and guidance.\n\nSincerely,\n[Employee Name]\n[Designation]\n[Department]\n[Employee ID]",
                    ],
                ],
            ],

            'recruitment' => [
                'name' => 'Recruitment, Offers & Onboarding',
                'icon' => 'bx-user-plus',
                'badge' => 'Talent Acquisition',
                'templates' => [
                    'offer_software_engineer' => [
                        'name' => 'Job Offer Letter - Software Engineer / Full Stack',
                        'title' => 'Official Job Offer Letter (Software Engineer)',
                        'subject' => 'Offer of Employment: Software Development Engineer at [Company Name]',
                        'description' => 'Comprehensive job offer letter for software development and engineering roles.',
                        'content' => "Subject: Offer of Employment: Software Development Engineer at [Company Name]\n\nDate: [Date]\n\nTo,\n[Candidate Name]\n[Candidate Address / Email]\n\nDear [Candidate Name],\n\nWe are delighted to extend a formal offer of employment for the position of Software Development Engineer at [Company Name]. Your technical acumen, problem-solving capabilities, and engineering mindset demonstrated throughout our evaluation cycles make you an exceptional fit for our technology division.\n\nKey Terms of Your Offer:\n1. Designation: Software Development Engineer (Full Stack / Backend)\n2. Department: Engineering & Product Development\n3. Annual CTC: [Annual CTC Amount] ([Salary in Words]) per annum, inclusive of statutory benefits.\n4. Date of Joining: [Joining Date]\n5. Work Location: [Office Location / Hybrid / Remote]\n6. Reporting To: [Reporting Manager / Engineering Director]\n\nThis offer is contingent upon successful background verification and receipt of all academic and professional credentials. Please review the attached detailed compensation breakdown and return a signed copy of this letter within five (5) business days to confirm your acceptance.\n\nWe look forward to building groundbreaking digital products together.\n\nWarm regards,\n\nAuthorized Signatory\n[HR Manager / Director of Talent Acquisition]\n[Company Name]",
                    ],
                    'offer_qa_engineer' => [
                        'name' => 'Job Offer Letter - QA & Test Automation Engineer',
                        'title' => 'Official Job Offer Letter (QA Automation Engineer)',
                        'subject' => 'Offer of Employment: Quality Assurance Automation Engineer at [Company Name]',
                        'description' => 'Job offer letter for QA engineers, test automation specialists, and SDETs.',
                        'content' => "Subject: Offer of Employment: Quality Assurance Automation Engineer at [Company Name]\n\nDate: [Date]\n\nTo,\n[Candidate Name]\n[Candidate Address / Email]\n\nDear [Candidate Name],\n\nOn behalf of [Company Name], we are pleased to offer you the position of Quality Assurance Automation Engineer. We were highly impressed with your test framework expertise, automation architecture skills, and commitment to software quality.\n\nKey Offer Details:\n- Role: QA Automation Engineer (SDET)\n- Fixed & Variable Compensation: [Annual CTC Amount] per annum\n- Proposed Joining Date: [Joining Date]\n- Work Schedule: Full-Time (Monday to Friday)\n- Reporting Lead: Head of Quality Engineering\n\nPlease confirm your acceptance by countersigning this letter and submitting your onboarding documentation.\n\nWelcome aboard to our engineering family!\n\nSincerely,\n\n[Authorized Signatory Name]\nHead of Human Resources\n[Company Name]",
                    ],
                    'offer_devops_engineer' => [
                        'name' => 'Job Offer Letter - Cloud / DevOps Engineer',
                        'title' => 'Official Job Offer Letter (DevOps & Cloud Engineer)',
                        'subject' => 'Offer of Employment: DevOps & Cloud Infrastructure Engineer at [Company Name]',
                        'description' => 'Job offer letter for DevOps, SRE, and Cloud Architects.',
                        'content' => "Subject: Offer of Employment: DevOps & Cloud Infrastructure Engineer at [Company Name]\n\nDate: [Date]\n\nTo,\n[Candidate Name]\n[Candidate Address]\n\nDear [Candidate Name],\n\nFollowing our recent interview discussions, [Company Name] is thrilled to offer you the position of Cloud & DevOps Engineer. In this role, you will champion our Kubernetes cloud infrastructure, CI/CD orchestration pipelines, and enterprise security posture.\n\nOffer Overview:\n- Role: DevOps & Infrastructure Engineer\n- Total Remuneration: [Annual CTC Amount] per annum\n- Effective Start Date: [Joining Date]\n- Employment Type: Permanent, Full-Time\n\nKindly sign and return the acceptance copy to initiate your IT asset procurement and onboarding roadmap.\n\nWarm regards,\n\nAuthorized Signatory\nHuman Resources Division\n[Company Name]",
                    ],
                    'appointment_letter' => [
                        'name' => 'Formal Appointment Letter & Employment Contract',
                        'title' => 'Formal Appointment Letter & Service Agreement',
                        'subject' => 'Letter of Appointment - [Employee Name] ([Designation])',
                        'description' => 'Comprehensive corporate appointment letter detailing employment terms, duties, and IP clauses.',
                        'content' => "Ref: REF/[Year]/APPT-[Ref Code]\nDate: [Date]\n\nTo,\n[Employee Name]\n[Employee ID]\n[Address Line]\n\nDear [Employee Name],\n\nWe take immense pleasure in appointing you as [Designation] in our organization [Company Name], with effect from [Effective Date], subject to the following terms and conditions:\n\n1. Role & Responsibilities: You will perform all duties pertaining to [Designation] and any other responsibilities assigned by management.\n2. Remuneration: Your annual Gross Cost to Company (CTC) shall be [Annual CTC Amount] as detailed in Annexure A.\n3. Probation Period: You will be on probation for a period of [Probation Period, e.g., 3 months / 6 months] from the date of joining.\n4. Intellectual Property & Confidentiality: All intellectual property, codebases, frameworks, and confidential customer intelligence developed during your tenure shall belong exclusively to [Company Name].\n5. Termination & Notice Period: Either party may terminate this agreement by providing [Notice Period, e.g., 30 days / 60 days] written notice or salary in lieu thereof.\n\nPlease sign and return the duplicate copy of this letter as an endorsement of your acceptance.\n\nFor [Company Name],\n\nAuthorized Signatory\nDirector / Head of Human Resources",
                    ],
                    'internship_offer' => [
                        'name' => 'Internship Offer Letter (Software / Web Engineering)',
                        'title' => 'Internship Offer & Training Agreement',
                        'subject' => 'Offer of Engineering Internship at [Company Name]',
                        'description' => 'Internship offer for engineering students, trainees, and junior interns.',
                        'content' => "Date: [Date]\n\nTo,\n[Intern Name]\n[College / University Name]\n[Email Address]\n\nDear [Intern Name],\n\nWe are pleased to offer you an internship position as Software Engineering Intern at [Company Name]. We were impressed by your academic track record and enthusiasm for software innovation.\n\nInternship Details:\n- Internship Duration: [Start Date] to [End Date] ([Duration, e.g. 6 Months])\n- Monthly Stipend: [Stipend Amount, e.g. INR 25,000 / month]\n- Department: Product Engineering\n- Mentor / Supervisor: [Mentor Name / Tech Lead]\n\nDuring this tenure, you will gain hands-on production experience on enterprise web applications, code reviews, agile sprints, and modern CI/CD systems. Upon successful completion, exemplary performance will be considered for a full-time pre-placement offer (PPO).\n\nCongratulations and welcome to the team!\n\nSincerely,\n\nAuthorized Signatory\nCampus & University Relations\n[Company Name]",
                    ],
                    'probation_confirmation' => [
                        'name' => 'Probation Confirmation & Permanent Status Letter',
                        'title' => 'Confirmation of Employment Post Probation',
                        'subject' => 'Confirmation of Employment - [Employee Name] ([Employee ID])',
                        'description' => 'Formal letter confirming the successful completion of employee probation period.',
                        'content' => "Date: [Date]\n\nTo,\n[Employee Name]\n[Designation]\n[Employee ID]\n\nDear [Employee Name],\n\nWe are pleased to inform you that you have successfully completed your probation period with [Company Name] effective [Confirmation Date].\n\nManagement has thoroughly appraised your performance, domain competence, and commitment to organizational milestones. We are delighted to confirm your status as a Permanent Employee of [Company Name].\n\nAll other terms and conditions of your original appointment letter remain intact. We trust you will continue to deliver excellence and lead our engineering initiatives to greater heights.\n\nCongratulations on reaching this milestone in your career with us!\n\nYours sincerely,\n\nAuthorized Signatory\nHead of Human Resources & Operations\n[Company Name]",
                    ],
                ],
            ],

            'performance' => [
                'name' => 'Performance, Appraisal & Rewards',
                'icon' => 'bx-trending-up',
                'badge' => 'HR Compensation',
                'templates' => [
                    'salary_increment' => [
                        'name' => 'Annual Salary Increment & Appraisal Letter',
                        'title' => 'Annual Performance Appraisal & Compensation Revision',
                        'subject' => 'Annual Performance Appraisal & Salary Increment - FY [Fiscal Year]',
                        'description' => 'Formal compensation increment letter highlighting performance review and new CTC.',
                        'content' => "Date: [Date]\n\nTo,\n[Employee Name]\n[Designation]\n[Department]\n[Employee ID]\n\nDear [Employee Name],\n\nIn recognition of your dedicated contributions, technical leadership, and exemplary performance during the fiscal year [Fiscal Year], the Management is pleased to announce a revision in your compensation package effective [Effective Date].\n\nYour revised compensation structure is as follows:\n- Previous Annual CTC: [Old CTC Amount]\n- Increment Percentage: [Increment Percentage]%\n- Revised Annual CTC: [New CTC Amount] ([New CTC in Words])\n\nA comprehensive breakdown of your monthly salary components, allowances, and statutory benefits is outlined in the attached Annexure.\n\nWe appreciate your dedication to [Company Name] and look forward to your continuous impact and professional growth.\n\nWarm congratulations,\n\nAuthorized Signatory\nManaging Director / Chief Executive Officer\n[Company Name]",
                    ],
                    'promotion_letter' => [
                        'name' => 'Promotion & Designation Upgrade Letter',
                        'title' => 'Career Advancement & Promotion Notification',
                        'subject' => 'Promotion Notification: Elevation to [New Designation]',
                        'description' => 'Formal promotion letter elevating employee to higher tier or leadership role.',
                        'content' => "Date: [Date]\n\nTo,\n[Employee Name]\n[Current Designation]\n[Employee ID]\n\nDear [Employee Name],\n\nOn behalf of the Executive Leadership team, we are thrilled to congratulate you on your promotion to the position of [New Designation] at [Company Name], effective [Effective Date].\n\nThis promotion recognizes your stellar architectural leadership, mentorship of junior engineers, and consistent delivery of high-impact technical initiatives. In your new role, you will spearhead [briefly outline key new responsibilities / team leadership].\n\nYour revised compensation and grade band details are enclosed. We have absolute confidence in your ability to drive strategic innovation in this expanded role.\n\nCongratulations on this well-deserved career achievement!\n\nSincerely,\n\nAuthorized Signatory\nChief Technology Officer / HR Director\n[Company Name]",
                    ],
                    'performance_bonus' => [
                        'name' => 'Annual Performance Bonus & Incentive Grant',
                        'title' => 'Performance Bonus & Achievement Award Letter',
                        'subject' => 'Grant of Annual Performance Bonus - FY [Fiscal Year]',
                        'description' => 'Letter acknowledging extraordinary performance and granting a financial bonus.',
                        'content' => "Date: [Date]\n\nTo,\n[Employee Name]\n[Designation]\n[Employee ID]\n\nDear [Employee Name],\n\nIn appreciation of your exceptional contributions to the successful delivery of [Key Project / Strategic Milestone] during [Fiscal Year], Management is delighted to award you a special Performance Bonus of [Bonus Amount] ([Amount in Words]).\n\nThis one-time performance incentive will be disbursed alongside your upcoming payroll cycle for [Month, Year], subject to applicable statutory tax deductions.\n\nThank you for demonstrating unwavering commitment to our company's core values and technical benchmarks.\n\nBest regards,\n\nAuthorized Signatory\nFinance & HR Committee\n[Company Name]",
                    ],
                    'warning_pip_letter' => [
                        'name' => 'Performance Warning / PIP (Performance Improvement Plan)',
                        'title' => 'Official Performance Advisory & Improvement Plan',
                        'subject' => 'CONFIDENTIAL: Formal Performance Advisory & 30-Day PIP Notice',
                        'description' => 'Structured performance improvement notice outlining gap areas, goals, and evaluation timeline.',
                        'content' => "STRICTLY PRIVATE & CONFIDENTIAL\n\nDate: [Date]\n\nTo,\n[Employee Name]\n[Designation]\n[Employee ID]\n\nDear [Employee Name],\n\nThis letter serves as formal notification regarding your recent performance evaluations in the role of [Designation]. Over the past [Evaluation Period, e.g. 3 months], several performance benchmarks and sprint deliverables have fallen below organizational standards, specifically in:\n1. [Area 1: e.g. Code quality and defect rate during sprint cycles]\n2. [Area 2: e.g. Adherence to milestone deadlines and client commitments]\n\nTo assist you in aligning with expected performance metrics, you are placed on a 30-Day Performance Improvement Plan (PIP) effective [Start Date] through [End Date]. Your reporting manager, [Manager Name], will conduct weekly 1-on-1 reviews to provide guidance and evaluate your progress against the targets defined in Annexure A.\n\nPlease note that failure to meet these mandatory deliverables by the conclusion of the PIP period may result in further administrative action, up to and including separation of employment.\n\nWe encourage you to utilize all available organizational resources and mentors during this period.\n\nSincerely,\n\nAuthorized Signatory\nHead of Human Resources & Engineering\n[Company Name]",
                    ],
                ],
            ],

            'certificates' => [
                'name' => 'Certificates, Verification & Authorization',
                'icon' => 'bx-award',
                'badge' => 'Official Verification',
                'templates' => [
                    'experience_certificate' => [
                        'name' => 'Experience Certificate / Service Letter',
                        'title' => 'Official Experience & Service Certificate',
                        'subject' => 'Experience Certificate - [Employee Name]',
                        'description' => 'Comprehensive experience certificate certifying tenure, designation, and professional conduct.',
                        'content' => "TO WHOMSOEVER IT MAY CONCERN\n\nDate: [Date]\nRef: EXP/[Year]/[Ref No]\n\nThis is to certify that [Employee Name] was employed with [Company Name] as a full-time [Designation] from [Start Date] to [End Date].\n\nDuring their tenure with us, [Employee Name] was actively engaged in architecting, developing, and deploying enterprise-grade digital systems. They consistently exhibited strong technical acumen, sound work ethics, and excellent collaborative skills.\n\nTheir conduct and professional character were exemplary throughout their service. [Employee Name] has completed all formal clearance procedures with no outstanding organizational liabilities.\n\nWe wish [Employee Name] the very best in all their future career endeavors.\n\nFor [Company Name],\n\nAuthorized Signatory\nDirector / Head of Human Resources\n[Company Seal]",
                    ],
                    'relieving_letter' => [
                        'name' => 'Relieving Letter & Discharge Certificate',
                        'title' => 'Official Relieving Letter Post Resignation',
                        'subject' => 'Relieving Order - [Employee Name] ([Employee ID])',
                        'description' => 'Official relieving letter certifying formal release from company duties.',
                        'content' => "Date: [Date]\nRef: REL/[Year]/[Ref No]\n\nTo,\n[Employee Name]\n[Designation]\n[Employee ID]\n\nDear [Employee Name],\n\nSubject: Relieving Order\n\nWith reference to your resignation letter dated [Resignation Date], we confirm that you are formally relieved from your duties and responsibilities as [Designation] at [Company Name] with effect from the close of business hours on [Relieving Date].\n\nWe acknowledge that you have successfully completed all knowledge transfer (KT) sessions, handed over company assets and repositories, and cleared all administrative dues. Your final full & final settlement (F&F) will be processed in accordance with standard payroll schedules.\n\nWe appreciate the contributions made by you during your service and wish you continued success in your future endeavors.\n\nSincerely,\n\nAuthorized Signatory\nHuman Resources Division\n[Company Name]",
                    ],
                    'bonafide_letter' => [
                        'name' => 'Bonafide / Employment Verification (Visa / Bank Loan)',
                        'title' => 'Employment Verification & Bonafide Certificate',
                        'subject' => 'Employment Verification Letter for [Employee Name] - [Purpose]',
                        'description' => 'Standard verification letter for visa applications, embassies, rental, or bank loan approvals.',
                        'content' => "TO WHOMSOEVER IT MAY CONCERN\n\nDate: [Date]\n\nThis letter is issued to confirm that [Employee Name] is a permanent, full-time employee of [Company Name] since [Joining Date], currently holding the designation of [Designation] in our [Department] division.\n\nKey Employment Credentials:\n- Employee ID: [Employee ID]\n- Official Email: [Employee Email]\n- Current Gross Annual Remuneration: [Annual CTC Amount] per annum\n- Employment Status: Active, Permanent\n- Work Location: [Office Address]\n\nThis certificate is issued upon the specific request of the employee for the purpose of [Purpose, e.g. Visa Processing / Mortgage Application / Address Verification] without any financial liability on the part of [Company Name].\n\nShould you require any further validation, please contact our HR department directly at [HR Email] or [HR Phone].\n\nFor [Company Name],\n\nAuthorized Signatory\nCorporate HR Operations\n[Company Name]",
                    ],
                    'internship_completion' => [
                        'name' => 'Internship Completion Certificate & Recommendation',
                        'title' => 'Internship Completion Certificate',
                        'subject' => 'Certificate of Internship Completion - [Intern Name]',
                        'description' => 'Certificate certifying internship completion and praising technical contributions.',
                        'content' => "CERTIFICATE OF INTERNSHIP COMPLETION\n\nDate: [Date]\nCertificate ID: INT-[Year]-[Ref Code]\n\nThis is to certify that [Intern Name], a student of [College / University Name], has successfully completed a [Duration, e.g. 6-Month] engineering internship with [Company Name] from [Start Date] to [End Date].\n\nDuring this tenure, [Intern Name] worked within our [Department / Technology Domain, e.g. Full Stack Web Development] team on real-world client architectures. They demonstrated remarkable curiosity, problem-solving skills, and code hygiene.\n\nWe found [Intern Name] to be proactive, diligent, and result-oriented. We commend their dedication and wish them glorious success in their upcoming academic and professional journey.\n\nAuthorized Signatory\nChief Technology Officer / HR Lead\n[Company Name]",
                    ],
                    'wfh_authorization' => [
                        'name' => 'Work From Home (WFH) / Remote Work Authorization',
                        'title' => 'Remote Work & Telecommuting Authorization',
                        'subject' => 'Authorization for Remote Work / WFH Arrangement - [Employee Name]',
                        'description' => 'Formal authorization letter granting approved remote work or hybrid telecommuting.',
                        'content' => "Date: [Date]\n\nTo,\n[Employee Name]\n[Designation]\n[Employee ID]\n\nDear [Employee Name],\n\nSubject: Authorization for Remote Work Arrangement\n\nIn accordance with your request and departmental approval, Management is pleased to approve your transition to a [Full-Time Remote / Hybrid] work schedule effective [Start Date] through [End Date / Ongoing].\n\nKey Guidelines for Remote Telecommuting:\n1. Core Availability: You are expected to be available on Slack/Teams and email during standard business hours ([Working Hours, e.g. 9:30 AM to 6:30 PM IST]).\n2. Security Protocols: All engineering activities must strictly utilize authorized VPN tunnels and company encrypted hardware.\n3. Milestone Tracking: Daily standup participation and sprint commit logs remain mandatory.\n\nManagement reserves the right to review or amend this remote arrangement based on project deliverables or organizational requirements.\n\nSincerely,\n\nAuthorized Signatory\nOperations & HR Management\n[Company Name]",
                    ],
                    'asset_handover' => [
                        'name' => 'Asset & Hardware Custody Undertaking Letter',
                        'title' => 'IT Asset Allocation & Custody Undertaking',
                        'subject' => 'IT Asset Allocation & Responsibility Undertaking - [Employee Name]',
                        'description' => 'Official document recording laptop, monitors, and accessories issued to employee.',
                        'content' => "Date: [Date]\n\nTo,\n[Employee Name]\n[Designation]\n[Employee ID]\n\nDear [Employee Name],\n\nSubject: IT Hardware & Asset Allocation\n\nThis document confirms the allocation of official company IT assets to support your professional duties at [Company Name].\n\nAllocated Equipment Details:\n1. Laptop Model & Specs: [Laptop Brand/Model, e.g. MacBook Pro 16\" / Dell XPS 15]\n2. Serial Number / Asset Tag: [Asset Serial Number]\n3. Accessories: [Power Adapter, USB-C Dock, Security Token, Laptop Bag]\n\nBy accepting these items, you acknowledge that the equipment remains the sole property of [Company Name] and must be maintained in good working condition. In the event of damage, theft, or separation of employment, all assets must be returned promptly to the IT Helpdesk.\n\nReceived & Acknowledged by:\n\n_______________________\n[Employee Signature & Date]\n[Employee Name] ([Employee ID])\n\nIssued by:\nIT Infrastructure Department\n[Company Name]",
                    ],
                ],
            ],

            'legal' => [
                'name' => 'Legal, IP & Separation',
                'icon' => 'bx-shield-quarter',
                'badge' => 'Legal & Compliance',
                'templates' => [
                    'nda_agreement' => [
                        'name' => 'Non-Disclosure Agreement (NDA) & IP Undertaking',
                        'title' => 'Employee Non-Disclosure & Confidentiality Undertaking',
                        'subject' => 'Confidentiality & Proprietary Rights Undertaking - [Employee Name]',
                        'description' => 'Legally binding non-disclosure, trade secrets, and intellectual property agreement.',
                        'content' => "EMPLOYEE NON-DISCLOSURE AND PROPRIETARY INFORMATION AGREEMENT\n\nDate: [Date]\n\nThis Agreement is entered into between [Company Name] (\"Company\") and [Employee Name], residing at [Address] (\"Employee\").\n\n1. Confidential Information: Employee understands that during the course of employment, they will have access to proprietary data, source codes, algorithmic models, customer databases, and commercial strategies.\n2. Non-Disclosure Duty: Employee agrees to hold all Confidential Information in strict trust and confidence, and shall not disclose or duplicate such information without prior written executive authorization.\n3. Ownership of Inventions: All code, discoveries, designs, and documentation authored or created by Employee within the scope of employment belong exclusively and perpetually to [Company Name].\n4. Post-Separation Covenants: The non-disclosure obligations defined herein shall survive the termination or expiration of employment for a period of [Duration, e.g. 3 years].\n\nIN WITNESS WHEREOF, the parties execute this Agreement as of the date first stated above.\n\nFor [Company Name]:                               Employee:\n\n__________________________                         __________________________\nAuthorized Signatory                              [Employee Name]\nTitle: Director of Legal & Compliance             [Employee ID]",
                    ],
                    'resignation_acceptance' => [
                        'name' => 'Resignation Acceptance Letter',
                        'title' => 'Formal Acceptance of Resignation',
                        'subject' => 'Acceptance of Resignation - [Employee Name] ([Employee ID])',
                        'description' => 'Formal response accepting an employee resignation and detailing exit schedule.',
                        'content' => "Date: [Date]\n\nTo,\n[Employee Name]\n[Designation]\n[Employee ID]\n\nDear [Employee Name],\n\nSubject: Acceptance of Resignation\n\nWe are in receipt of your formal resignation email/letter dated [Resignation Date], requesting release from your employment as [Designation] at [Company Name].\n\nManagement has accepted your resignation. In accordance with our standard notice period terms, your last working day (LWD) with [Company Name] will be [Last Working Date].\n\nPlease coordinate closely with your manager, [Manager Name], to complete seamless knowledge transfer to designated teammates, hand over all repositories and documentation, and complete exit formalities.\n\nWe thank you for your service and wish you the very best in your subsequent professional endeavors.\n\nYours sincerely,\n\nAuthorized Signatory\nHuman Resources Division\n[Company Name]",
                    ],
                    'termination_notice' => [
                        'name' => 'Termination / Separation Notice',
                        'title' => 'Official Notice of Employment Separation',
                        'subject' => 'STRICTLY CONFIDENTIAL: Notice of Employment Termination',
                        'description' => 'Formal separation notification stating effective release date and severance details.',
                        'content' => "STRICTLY PRIVATE & CONFIDENTIAL\n\nDate: [Date]\n\nTo,\n[Employee Name]\n[Designation]\n[Employee ID]\n\nDear [Employee Name],\n\nThis letter serves as formal notification that your employment with [Company Name] as [Designation] is being terminated with effect from the close of business on [Effective Separation Date].\n\nThis administrative decision follows [briefly mention reason, e.g. conclusion of the Performance Improvement Plan / organizational restructuring / policy breach as documented on [Date]].\n\nYour final full & final settlement (F&F), including earned wages, accrued leave encashment, and any applicable severance, will be disbursed following complete handover of company assets and repositories.\n\nPlease schedule an exit meeting with HR at your earliest convenience.\n\nSincerely,\n\nAuthorized Signatory\nExecutive Management & Human Resources\n[Company Name]",
                    ],
                    'noc_letter' => [
                        'name' => 'No Objection Certificate (NOC)',
                        'title' => 'Official No Objection Certificate',
                        'subject' => 'No Objection Certificate - [Employee Name]',
                        'description' => 'Standard No Objection Certificate for higher studies, passport, or freelance permissions.',
                        'content' => "NO OBJECTION CERTIFICATE (NOC)\n\nDate: [Date]\nRef: NOC/[Year]/[Ref No]\n\nTO WHOMSOEVER IT MAY CONCERN\n\nThis is to certify that [Employee Name] is currently employed with [Company Name] as a full-time [Designation] since [Joining Date].\n\n[Company Name] has NO OBJECTION to [Employee Name] pursuing [Purpose, e.g. Part-Time Master of Technology Degree / Passport Renewal / International Conference Travel] during [Timeframe], provided it does not interfere with their core employment obligations.\n\nThis certificate is issued at the specific request of the employee without any financial or legal commitment on behalf of the company.\n\nFor [Company Name],\n\nAuthorized Signatory\nHead of Administration & HR\n[Company Seal]",
                    ],
                ],
            ],
        ];
    }

    /**
     * Get flat list of all templates across categories.
     */
    public static function getAllTemplates(): array
    {
        $all = [];
        foreach (self::getCategories() as $catKey => $cat) {
            foreach ($cat['templates'] as $tplKey => $tpl) {
                $tpl['category_key'] = $catKey;
                $tpl['category_name'] = $cat['name'];
                $all[$tplKey] = $tpl;
            }
        }

        return $all;
    }

    /**
     * Find a specific template by key.
     */
    public static function getTemplate(string $key): ?array
    {
        $all = self::getAllTemplates();

        return $all[$key] ?? ($all['apology_leave'] ?? null);
    }

    /**
     * Replace dynamic placeholders in content.
     */
    public static function render(string $templateContent, array $replacements = []): string
    {
        $defaults = [
            '[Date]' => now()->format('F d, Y'),
            '[Year]' => now()->format('Y'),
            '[Fiscal Year]' => now()->format('Y') . '-' . (now()->format('y') + 1),
            '[Company Name]' => 'Bengal IT Hub Private Limited',
            '[Employee Name]' => 'Alexander Wright',
            '[Candidate Name]' => 'David Vance',
            '[Intern Name]' => 'Sophia Reynolds',
            '[Designation]' => 'Senior Software Engineer',
            '[Current Designation]' => 'Software Engineer II',
            '[New Designation]' => 'Lead Software Development Engineer',
            '[Department]' => 'Enterprise Cloud Solutions',
            '[Department / Team]' => 'Core Engineering Team',
            '[Employee ID]' => 'EMP-' . date('Y') . '-0842',
            '[Start Date]' => now()->subDays(3)->format('F d, Y'),
            '[End Date]' => now()->format('F d, Y'),
            '[Joining Date]' => now()->addDays(14)->format('F d, Y'),
            '[Confirmation Date]' => now()->format('F d, Y'),
            '[Effective Date]' => now()->format('F d, Y'),
            '[Relieving Date]' => now()->format('F d, Y'),
            '[Resignation Date]' => now()->subDays(30)->format('F d, Y'),
            '[Last Working Date]' => now()->format('F d, Y'),
            '[Leave Type]' => 'Medical / Emergency',
            '[Annual CTC Amount]' => 'INR 18,50,000 / $85,000',
            '[Old CTC Amount]' => 'INR 15,00,000',
            '[New CTC Amount]' => 'INR 18,50,000',
            '[New CTC in Words]' => 'Eighteen Lakhs Fifty Thousand Only',
            '[Increment Percentage]' => '23.3',
            '[Bonus Amount]' => 'INR 1,50,000 ($2,000)',
            '[Amount in Words]' => 'One Lakh Fifty Thousand Only',
            '[Probation Period]' => '6 (Six) Months',
            '[Duration]' => '6 Months',
            '[Notice Period]' => '60 (Sixty) Days',
            '[Office Location]' => 'Kolkata, India (Hybrid)',
            '[Office Address]' => '3rd Floor 259, New Santoshpur Main Rd, Santoshpur, Kolkata 700075, India',
            '[Reporting Manager]' => 'Director of Engineering',
            '[Mentor Name]' => 'Arun Kumar (Principal Architect)',
            '[Project Name]' => 'PMS Enterprise Ecosystem',
            '[Incident / Service Name]' => 'Payment Gateway & Cloud Sync Service',
            '[Build Version]' => 'v4.2.1-prod',
            '[Laptop Brand/Model]' => 'Apple MacBook Pro 16" M3 Max (36GB RAM, 1TB SSD)',
            '[Asset Serial Number]' => 'C02G8942MD6R',
            '[HR Email]' => 'hr@bengalithub.com',
            '[HR Phone]' => '+91 92306 53975',
            '[Employee Email]' => 'alexander.w@bengalithub.com',
            '[Ref No]' => strtoupper(substr(md5(time()), 0, 6)),
            '[Ref Code]' => date('Y') . '-' . strtoupper(substr(md5(time()), 0, 4)),
            '[Authorized Signatory Name]' => 'Arthur Pendelton',
        ];

        $merged = array_merge($defaults, $replacements);

        return str_replace(array_keys($merged), array_values($merged), $templateContent);
    }
}
