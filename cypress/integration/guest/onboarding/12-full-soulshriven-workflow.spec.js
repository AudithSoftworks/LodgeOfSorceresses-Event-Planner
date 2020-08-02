import { noAttendances } from "../../../fixtures/xhr-operations/attendances/no-attendances";
import { content } from "../../../fixtures/xhr-operations/content";
import { soulshriven as stubFetchingCmsContentForOnboardingSteps } from "../../../fixtures/xhr-operations/onboarding/soulshriven";
import { sets } from "../../../fixtures/xhr-operations/sets";
import { skills } from "../../../fixtures/xhr-operations/skills";
import { teams } from "../../../fixtures/xhr-operations/teams";
import { noCharacters } from "../../../fixtures/xhr-operations/users/@me/characters/no-characters";
import { stubGuest } from "../../../fixtures/xhr-operations/users/@me/guest";
import { stubSoulshrivenWithNoNameAndNoForumOauth, stubUpdateName } from "../../../fixtures/xhr-operations/users/@me/soulshriven";

describe('New User - Onboarding - Full Soulshriven Workflow', function () {
    it('user clicks "Soulshriven" link, initiates Onboarding Wizard', function () {
        cy.server();

        stubGuest(cy);
        stubFetchingCmsContentForOnboardingSteps(cy);

        cy.visit('/');
        cy.get('h2[data-cy="loading"]').contains('Checking session...');
        cy.wait('@loadGuestUser')
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/home');

        cy.get('article.membership-mode-selection > a[data-heading="Soulshriven"]').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/onboarding/soulshriven');
        cy.get('h2[data-cy="loading"]').contains('Fetching content...');
        cy.wait('@loadOnboardingSoulshrivenStep1');

        cy.get('h2').contains('Step 1/3:');
        cy.get('article.cms-content + span').find('button').should('have.length', 3);
        cy.get('article.cms-content + span > button:nth-of-type(1)').contains('Back');
        cy.get('article.cms-content + span > button:nth-of-type(2)').contains('Cancel the application & DELETE my account!');
        cy.get('article.cms-content + span > button:nth-of-type(3)').contains('Continue');
    });

    it('user goes through 3 Onboarding steps and completes Onboarding as a Soulshriven', function () {
        cy.server();
        stubGuest(cy);

        cy.visit('/onboarding/soulshriven');
        cy.get('h2[data-cy="loading"]').contains('Checking session...');
        cy.wait('@loadGuestUser')

        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/onboarding/soulshriven');
        cy.get('h2[data-cy="loading"]').contains('Loading...'); // Suspense lazy loading of components

        stubFetchingCmsContentForOnboardingSteps(cy);
        cy.request('GET', '/api/onboarding/soulshriven/content/by-step/1');
        cy.get('h2[data-cy="loading"]').contains('Fetching content...');
        cy.wait('@loadOnboardingSoulshrivenStep1');
        cy.get('h2').contains('Step 1/3:');
        cy.get('article.cms-content + span').find('button').should('have.length', 3);
        cy.get('article.cms-content + span > button:nth-of-type(1)').contains('Back');
        cy.get('article.cms-content + span > button:nth-of-type(2)').contains('Cancel the application & DELETE my account!');
        cy.get('article.cms-content + span > button:nth-of-type(3)').contains('Continue');

        cy.get('article.cms-content + span > button:nth-of-type(3)').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/onboarding/soulshriven');
        cy.get('h2[data-cy="loading"]').contains('Fetching content...');
        cy.wait('@loadOnboardingSoulshrivenStep2');
        cy.get('h2').contains('Step 2/3:');
        cy.get('article.cms-content + span').find('button').should('have.length', 3);
        cy.get('article.cms-content + span > button:nth-of-type(1)').contains('Back');
        cy.get('article.cms-content + span > button:nth-of-type(2)').contains('Cancel the application & DELETE my account!');
        cy.get('article.cms-content + span > button:nth-of-type(3)').contains('Continue');

        cy.get('article.cms-content + span > button:nth-of-type(3)').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/onboarding/soulshriven');
        cy.get('h2[data-cy="loading"]').contains('Fetching content...');
        cy.wait('@loadOnboardingSoulshrivenStep3');
        cy.get('h2').contains('Step 3/3:');
        cy.get('article.cms-content + span').find('button').should('have.length', 3);
        cy.get('article.cms-content + span > button:nth-of-type(1)').contains('Back');
        cy.get('article.cms-content + span > button:nth-of-type(2)').contains('Cancel the application & DELETE my account!');
        cy.get('article.cms-content + span > button:nth-of-type(3)').contains('Continue');

        cy.get('article.cms-content + span > button:nth-of-type(3)').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/onboarding/soulshriven');
        cy.get('h2').should('have.text', 'Joining the Guild as a Soulshriven');
        cy.get('article.cms-content + span').find('button').should('have.length', 3);
        cy.get('article.cms-content + span > button:nth-of-type(1)').contains('Back');
        cy.get('article.cms-content + span > button:nth-of-type(2)').contains('Cancel the application & DELETE my account!');
        cy.get('article.cms-content + span > button:nth-of-type(3)').contains('Accept & Join');

        cy.get('article.cms-content + span > button:nth-of-type(3)').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/home');
        cy.wait('@loadOnboardingSoulshrivenFinalize');
        cy.get('h2').should('have.text', 'Welcome, Soulless One!');
        cy.get('main > section > form.jumbotron').should('exist');
        cy.get('main > section > form.jumbotron > h3').should('have.text', 'Your ESO ID:');
        cy.get('main > section > form.jumbotron > input').should('exist');
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

    it('after Onboarding, on Dashboard, user updates their ESO-ID', function () {
        cy.server();
        stubSoulshrivenWithNoNameAndNoForumOauth(cy);

        cy.visit('/');
        cy.get('h2[data-cy="loading"]').contains('Checking session...');
        cy.wait('@loadSoulshrivenWithNoNameAndNoForumOauth')
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/home');

        cy.get('[data-cy="account-status-element"].danger > input[type="text"]')
            .type('SoulshrivenEsoId')
            .should('have.value', 'SoulshrivenEsoId');

        stubUpdateName(cy);
        sets(cy);
        skills(cy);
        content(cy);
        teams(cy);
        noCharacters(cy);
        noAttendances(cy);

        cy.get('[data-cy="account-status-element"].danger > input[type="submit"]').click();
        cy.wait('@updateSoulshrivenName');
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
