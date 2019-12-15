import { stubFetchingGroups } from "../fixtures/helpers/groups";
import { stubFetchingSets } from "../fixtures/helpers/sets";
import { stubFetchingSkills } from "../fixtures/helpers/skills";
import { stubSoulshrivenUserAfterNameChange } from "../fixtures/helpers/users/soulshriven-after-name-change";
import { stubSoulshrivenUserBeforeNameChange } from "../fixtures/helpers/users/soulshriven-before-name-change";

describe('Dashboard Screen for Soulshriven user', function () {
    it('detects heading "Account Status"', function () {
        cy.server();
        stubFetchingGroups(cy);
        stubSoulshrivenUserBeforeNameChange(cy);
        stubFetchingSets(cy);
        stubFetchingSkills(cy);
        cy.route('GET', '/api/users/@me/characters', []);

        cy.visit('/');
        cy.get('h2').should('have.text', 'Account Status');
        cy.get('[data-cy=account-status-element]').should('have.length', 2);
        cy.get('[data-cy="account-status-element"].success').should('have.length', 1);
        cy.get('[data-cy="account-status-element"].danger').should('have.length', 1);
        cy.get('[data-cy="account-status-element"].success > h3').should('have.text', 'Your Discord Account:');
        cy.get('[data-cy="account-status-element"].success > p').should('have.text', 'Linked');
        cy.get('[data-cy="account-status-element"].danger').should('be.match', 'form');
        cy.get('[data-cy="account-status-element"].danger > h3').should('have.text', 'Your ESO ID:');
    });

    it('updates user ESO ID', function () {
        cy.server();
        cy.route({
            method: 'POST',
            url: '/api/users/@me',
            status: 204,
            response: ''
        }).as('updateMePostRequest');
        stubSoulshrivenUserAfterNameChange(cy);
        cy.get('[data-cy="account-status-element"].danger > input[type="text"]').type('GelmirTester').should('have.value', 'GelmirTester');
        cy.get('[data-cy="account-status-element"].danger > input[type="submit"]').click();
        cy.get('@updateMePostRequest').should((response) => {
            expect(response.status).to.be.equal(204);
            expect(response.body).to.be.undefined;
        });
        cy.get('[data-cy="account-status-element"].danger').should('have.length', 0);
        cy.get('[data-cy="account-status-element"].success').should('have.length', 2);

        let userUpdatedNotificationMessage = cy.get('.react-notification-root ' +
            '> .notification-container-top-right ' +
            '> .notification-item-root ' +
            '> .notification-item ' +
            '> .notification-custom.notification-success ' +
            '> .notification-content ' +
            '> .notification-message');
        userUpdatedNotificationMessage
            .should('exist')
            .should('have.text', 'User updated.');
        userUpdatedNotificationMessage.click();
        userUpdatedNotificationMessage.should('not.exist');
    });
});
