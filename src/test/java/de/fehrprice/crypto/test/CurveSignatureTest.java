package de.fehrprice.crypto.test;

import static org.junit.jupiter.api.Assertions.assertEquals;

import org.junit.jupiter.api.AfterAll;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.Test;

import de.fehrprice.crypto.Curve25519;
import de.fehrprice.crypto.Ed25519;

public class CurveSignatureTest {
	
    public static boolean disableLongRunningTest = true;
	
	private static Ed25519 ed;
	
	@BeforeAll
	public static void setUp() throws Exception {
		ed = new Ed25519();
	}

	@AfterAll
	public static void tearDown() throws Exception {
	}

	/*
	 * String API tests
	 */

/*
ALGORITHM:
Ed25519
SECRET KEY:
9d61b19deffd5a60ba844af492ec2cc4
4449c5697b326919703bac031cae7f60
PUBLIC KEY:
d75a980182b10ab7d54bfed3c964073a
0ee172f3daa62325af021a68f707511a
MESSAGE (length 0 bytes):
SIGNATURE:
e5564300c360ac729086e2cc806e828a
84877f1eb8e5d974d873e06522490155
5fb8821590a33bacc61e39701cf9b46b
d25bf5f0595bbe24655141438e7a100b
 */
	/**
	 * Test ed25519 according to RFC 8032, section 7.1. test vectors
	 * Test single ed25519 examples
	 */
	@Test
	public void Test1() {
		String secretKeyString, publicKeyString, messageString, signatureString;
		secretKeyString = "9d61b19deffd5a60ba844af492ec2cc44449c5697b326919703bac031cae7f60";
		publicKeyString = "d75a980182b10ab7d54bfed3c964073a0ee172f3daa62325af021a68f707511a";
		messageString   = "";
		signatureString = "e5564300c360ac729086e2cc806e828a84877f1eb8e5d974d873e065224901555fb8821590a33bacc61e39701cf9b46bd25bf5f0595bbe24655141438e7a100b";
		//String pubk = ed.publicKey(null);
		String pubk = ed.publicKey(secretKeyString);
		assertEquals(publicKeyString, pubk);
	}

	@Test
	void testConstants() {
		assertEquals("46316835694926478169428394003475163141307993866256225615783033603165251855960", ed.By.toString());
		assertEquals("15112221349535400772501151409588531511454012693041857206046113283949847762202", ed.Bx.toString());
	}
}

