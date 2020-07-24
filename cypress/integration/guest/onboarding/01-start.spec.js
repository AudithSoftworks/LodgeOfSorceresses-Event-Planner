import { stubGuest } from "../../../fixtures/xhr-operations/users/@me/guest";

describe('Guest User - Onboarding - Start Point', function () {
    it('redirects to Dashboard and shows the beginning of Onboarding wizard (Member vs Soulshriven choice)', function () {
        cy.server();
        stubGuest(cy);

        cy.visit('/');
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/home');

        cy.get('header > ul.member-bar > li > figure > figcaption').should('have.text', 'user@...');
        cy.get('header > ul.member-bar > li.chevron > ul.member-bar-dropdown a[href="/logout"]').should('exist');
        cy.get('header > ul.member-bar > li.chevron > ul.member-bar-dropdown').should('be.hidden');

        cy.get('h2').should('have.text', 'Welcome, Soulless One!');
        cy.get('article.membership-mode-selection')
            .should('exist')
            .should('be.visible')
            .should('have.attr', 'data-text');
        cy.get('article.membership-mode-selection > a[data-heading="Member"]')
            .should('exist')
            .should('be.visible')
            .should('have.attr', 'data-text');
        cy.get('article.membership-mode-selection > a[data-heading="Soulshriven"]')
            .should('exist')
            .should('be.visible')
            .should('have.attr', 'data-text');
    });
});
