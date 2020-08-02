import { noAttendances } from "../../../fixtures/xhr-operations/attendances/no-attendances";
import { content } from "../../../fixtures/xhr-operations/content";
import { members as stubFetchingCmsContentForOnboardingSteps } from "../../../fixtures/xhr-operations/onboarding/members";
import { sets } from "../../../fixtures/xhr-operations/sets";
import { skills } from "../../../fixtures/xhr-operations/skills";
import { teams } from "../../../fixtures/xhr-operations/teams";
import { noCharacters } from "../../../fixtures/xhr-operations/users/@me/characters/no-characters";
import { stubGuest } from "../../../fixtures/xhr-operations/users/@me/guest";
import { stubMemberWithNoName, stubUpdateName } from "../../../fixtures/xhr-operations/users/@me/member";

describe('New User - Onboarding - Full Member Workflow', function () {
    it('user clicks "Member" link, initiates Onboarding Wizard', function () {
        cy.server();

        stubGuest(cy);
        stubFetchingCmsContentForOnboardingSteps(cy);

        cy.visit('/');
        cy.get('h2[data-cy="loading"]').contains('Checking session...');
        cy.wait('@loadGuestUser')
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/home');

        cy.get('article.membership-mode-selection > a[data-heading="Member"]').click();
        cy.request('GET', '/api/onboarding/members/content/by-step/1');
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/onboarding/members');
        cy.get('h2[data-cy="loading"]').contains('Fetching content...');
        cy.wait('@loadOnboardingMembersStep1');

        cy.get('h2').contains('Step 1/4:');
        cy.get('article.cms-content + span').find('button').should('have.length', 3);
        cy.get('article.cms-content + span > button:nth-of-type(1)').contains('Back');
        cy.get('article.cms-content + span > button:nth-of-type(2)').contains('Cancel the application & DELETE my account!');
        cy.get('article.cms-content + span > button:nth-of-type(3)').contains('Continue');
    });

    it('user goes through 4 Onboarding steps and completes Onboarding as a Member', function () {
        cy.server();
        stubGuest(cy);

        cy.visit('/onboarding/members');
        cy.get('h2[data-cy="loading"]').contains('Checking session...');
        cy.wait('@loadGuestUser')

        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/onboarding/members');
        cy.get('h2[data-cy="loading"]').contains('Loading...'); // Suspense lazy loading of components

        stubFetchingCmsContentForOnboardingSteps(cy);
        cy.request('GET', '/api/onboarding/members/content/by-step/1');
        cy.get('h2[data-cy="loading"]').contains('Fetching content...');
        cy.wait('@loadOnboardingMembersStep1');
        cy.get('h2').contains('Step 1/4:');
        cy.get('article.cms-content + span').find('button').should('have.length', 3);
        cy.get('article.cms-content + span > button:nth-of-type(1)').contains('Back');
        cy.get('article.cms-content + span > button:nth-of-type(2)').contains('Cancel the application & DELETE my account!');
        cy.get('article.cms-content + span > button:nth-of-type(3)').contains('Continue');

        cy.get('article.cms-content + span > button:nth-of-type(3)').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/onboarding/members');
        cy.get('h2[data-cy="loading"]').contains('Fetching content...');
        cy.wait('@loadOnboardingMembersStep2');
        cy.get('h2').contains('Step 2/4:');
        cy.get('article.cms-content + span').find('button').should('have.length', 3);
        cy.get('article.cms-content + span > button:nth-of-type(1)').contains('Back');
        cy.get('article.cms-content + span > button:nth-of-type(2)').contains('Cancel the application & DELETE my account!');
        cy.get('article.cms-content + span > button:nth-of-type(3)').contains('Continue');

        cy.get('article.cms-content + span > button:nth-of-type(3)').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/onboarding/members');
        cy.get('h2[data-cy="loading"]').contains('Fetching content...');
        cy.wait('@loadOnboardingMembersStep3');
        cy.get('h2').contains('Step 3/4:');
        cy.get('article.cms-content + span').find('button').should('have.length', 3);
        cy.get('article.cms-content + span > button:nth-of-type(1)').contains('Back');
        cy.get('article.cms-content + span > button:nth-of-type(2)').contains('Cancel the application & DELETE my account!');
        cy.get('article.cms-content + span > button:nth-of-type(3)').contains('Continue');

        cy.get('article.cms-content + span > button:nth-of-type(3)').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/onboarding/members');
        cy.get('h2[data-cy="loading"]').contains('Fetching content...');
        cy.wait('@loadOnboardingMembersStep4');
        cy.get('h2').contains('Step 4/4:');
        cy.get('article.cms-content + span').find('button').should('have.length', 3);
        cy.get('article.cms-content + span > button:nth-of-type(1)').contains('Back');
        cy.get('article.cms-content + span > button:nth-of-type(2)').contains('Cancel the application & DELETE my account!');
        cy.get('article.cms-content + span > button:nth-of-type(3)').contains('Continue');

        cy.get('article.cms-content + span > button:nth-of-type(3)').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/onboarding/members');
        cy.get('h2').should('have.text', 'Joining the Guild as a Member');
        cy.get('article.cms-content + span').find('button').should('have.length', 3);
        cy.get('article.cms-content + span > button:nth-of-type(1)').contains('Back');
        cy.get('article.cms-content + span > button:nth-of-type(2)').contains('Cancel the application & DELETE my account!');
        cy.get('article.cms-content + span > button:nth-of-type(3)').contains('Accept & Join');

        cy.get('article.cms-content + span > button:nth-of-type(3)').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/home');
        cy.wait('@loadOnboardingMembersFinalize');
        cy.get('h2').should('have.text', 'Welcome, Soulless One!');
        cy.get('main > section > article.jumbotron').should('exist');
        cy.get('main > section > article.jumbotron > h3').should('have.text', 'Your Lodge Forum Account:');
        cy.get('main > section > article.jumbotron > p').should('have.text', 'Not Linked');
        cy.get('.react-notification-root').should('exist');
        cy.get('.react-notification-root ' +
            '> .notification-container-top-right ' +
            '> .notification-item-root ' +
            '> .notification-item ' +
            '> .notification-custom ' +
            '> .notification-content ' +
            '> p.notification-message')
            .should('exist')
            .should('have.text', 'Onboarding complete.');
    });

    it('member with no ESO ID visits Dashboard, redirected to enter ESO ID', function () {
        cy.server();
        stubMemberWithNoName(cy);

        cy.visit('/');
        cy.get('h2[data-cy="loading"]').contains('Checking session...');
        cy.wait('@loadMemberWithNoName')
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/home');

        cy.get('[data-cy="account-status-element"].danger > input[type="text"]')
            .type('MemberEsoId')
            .should('have.value', 'MemberEsoId');

        stubUpdateName(cy);
        sets(cy);
        skills(cy);
        content(cy);
        teams(cy);
        noCharacters(cy);
        noAttendances(cy);

        cy.get('[data-cy="account-status-element"].danger > input[type="submit"]').click();
        cy.wait('@updateMemberName');
        let userUpdatedNotificationMessage = cy.get('.react-notification-root ' +
            '> .notification-container-top-right ' +
            '> .notification-item-root ' +
            '> .notification-item ' +
            '> .notification-custom.notification-success ' +
            '> .notification-content ' +
            '> .notification-message');
        userUpdatedNotificationMessage
            .should('exist')
            .should('have.text', 'User updated.')
        userUpdatedNotificationMessage.click();
        userUpdatedNotificationMessage.should('not.exist');

        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/');
        cy.get('h2[data-cy="loading"]').contains('Loading data...');
        cy.wait(['@loadSets', '@loadSkills', '@loadContent', '@loadTeams']);

        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/@me');
    });
});
