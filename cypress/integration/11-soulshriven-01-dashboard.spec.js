describe('Dashboard Screen for Soulshriven user', function () {
    it('detects heading "Account Status"', function () {
        cy.server();

        cy.fixture('users/soulshriven.json').as('authenticatedSoulshrivenUser');
        cy.route('GET', '/api/users/@me', '@authenticatedSoulshrivenUser');

        cy.fixture('groups.json').as('groups');
        cy.route('GET', '/api/groups', '@groups');
        cy.route('GET', '/api/users/@me/characters', []);

        cy.fixture('.sets.json').as('sets');
        cy.route('GET', '/api/sets', '@sets');

        cy.fixture('.skills.json').as('skills');
        cy.route('GET', '/api/skills', '@skills');

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
});
