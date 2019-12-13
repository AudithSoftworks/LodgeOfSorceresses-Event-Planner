import { stubFetchingGroups } from "../fixtures/helpers/groups";
import { stubFetchingSets } from "../fixtures/helpers/sets";
import { stubFetchingSkills } from "../fixtures/helpers/skills";
import { stubFetchingUsers } from "../fixtures/helpers/users";
import { stubSoulshrivenUserAfterNameChange } from "../fixtures/helpers/users/soulshriven-after-name-change";

describe('Dashboard Screen for Soulshriven user', function () {
    it('visits Roster page', function () {
        cy.server();
        stubSoulshrivenUserAfterNameChange(cy);
        stubFetchingGroups(cy);
        stubFetchingSets(cy);
        stubFetchingSkills(cy);
        stubFetchingUsers(cy);
        cy.route('GET', '/api/users/@me/characters', []);

        cy.visit('/');
        cy.contains('Roster').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/users');
        cy.get('h2').should('have.text', 'Fetching Roster information...');
        cy.get('h2').should('have.text', 'Roster');
        cy.get('ul.roster > li').should('have.length', 34);
    });
});
